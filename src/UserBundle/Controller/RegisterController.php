<?php

/*
 * This file is part of the Inspection Copy.
 * Copyright (C) 2019 Elsevier.
 * Created by mobelite.
 *
 * Date: 4/11/18
 * Time: 17:38
 * @author: Mobelite <www.mobelite.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace UserBundle\Controller;

use lib\Config;
use MBComponents\Controller\Controller;
use MBComponents\Services\GoogleReCaptcha;
use Symfony\Component\Form\FormInterface;
use UserBundle\Entity\RequestRegister;
use UserBundle\Entity\User;
use UserBundle\Form\RequestRegisterType;
use UserBundle\Services\UserService;
use MBComponents\Helpers\MainHelper;

/**
 * Class RegisterController
 * @package UserBundle\Controller
 */
class RegisterController extends Controller {

    /**
     * @return \Slim\Http\Response
     * @throws \Exception
     */
    public function register()
    {
        /** Get the utm_source from the url */
        $source     = '';
        $utm_source = $this->getApp()->request()->get('utm_source');
        if (isset($utm_source))
        {
            $source = $utm_source;
        }
        /** @var UserService $userService */
        $userService = $this->getSlx()->getUserService();
        /** @var  $lang */
        $lang = $this->language;
        /** @var RequestRegister $requestRegister */
        $requestRegister = new RequestRegister();
        /** @var FormInterface $form */
        $form = $this->createForm(RequestRegisterType::class, $requestRegister, ['language' => $lang]);
        $form->handleRequest($this->getRequest());
        /** Check if form is valid */
        if ($form->isSubmitted() && $form->isValid())
        {
            if (!$this->isRecaptchaValid())
            {
                return $this->renderJson([
                    'success' => false,
                    'recaptcha' => true,
                    'message' => $this->trans('error.captcha')
                ]);
            }
            $userRegistered = $userService->findUserByEmail($requestRegister->getEmail());
            /** get Entity manager  */
            $em = $this->getEntityManager();
            /** @var \DateTime $currentDate */
            $currentDate = new \DateTime('now');
            /** Delete old request for the current user */
            $em->getRepository(RequestRegister::class)->deleteOldRegistrationByEmail($requestRegister->getEmail());
            /** persist and flush the user registration with the email and the country only */
            $em->persist($requestRegister);
            $em->flush();
            /** Add user in SLX database if success send email */
            $user = new User();
            /** Fill user data from the registration request */
            $user->setEmail($requestRegister->getEmail());
            $user->setTitle($requestRegister->getTitle());
            $user->setFirstName($requestRegister->getFirstName());
            $this->language === 'in' ? $user->setMiddleName($requestRegister->getMiddleName()) : $user->setMiddleName('');
            $user->setLastName($requestRegister->getLastName());
            $user->setUrl($requestRegister->getUrl());
            $user->setPassword($requestRegister->getPassword());
            $user->setAcceptMarketing($requestRegister->isAcceptMarketing());
            $resultLogin = $userService->userLogin($user->getEmail(), $user->getPassword());
            /** send the country code to the webservice update user  */
            $ipAddress   = MainHelper::getClientIpAddress($this->getRequest()->server->all());
            $userCountry = $this->getSession()->get('countryCode-'.$ipAddress);
            if($userCountry !== null && $userCountry !== '')
            {
                /** Update user country */
                $user->setCountry($userCountry);
            }

            /** test if new user  */
            if (!$userRegistered)
            {
                $result = $userService->addUser($user);
                /** Test if the email is known to SLX */
            } elseif ((array_key_exists('source', $userRegistered) &&
                    !in_array($userRegistered['source'], Config::read('siteIds'))) &&
                isset($resultLogin['ErrorCode']) && $resultLogin['ErrorCode'] === '1105')
            {
                /** Get user ID */
                $user->setUserId($userRegistered['userId']);
                /** Update user registered but account not activated yet */
                $result = $userService->updateUser($user);

                /** Test if user account is not yet activated */
            } elseif (isset($resultLogin['ErrorCode']) && $resultLogin['ErrorCode'] === '1105')
            {
                /** Get user ID */
                $user->setUserId($userRegistered['userId']);
                /** Update user registered but account not activated yet */
                $result = $userService->updateUser($user);
                /** Test if user account is already activated */
            } else
            {
                /**
                 * The use is already registered from inspection copy
                 * send forget password email
                 */
                $userInfo = $userService->getUser($userRegistered['userId']);
                if ($this->language !== 'de')
                {
                    $emailBody = str_replace(
                        '__username__',
                        $userInfo->getFullName(),
                        $this->trans('user.register.email_user_from_ic.body')
                    );
                } else
                {
                    $emailBody = str_replace(array('__title__', '__lastname__'),
                        [$userInfo->getTitle(), $userInfo->getLastName()],
                        $this->trans('user.register.email_user_from_ic.body'));
                }
                $emailSubject = $this->trans('user.register.email_user_from_ic.subject');
                $emailButton = $this->trans('user.register.email_user_from_ic.button');

                $parameters = [
                    'dateTime' => $currentDate->format('Y-m-d') . 'Z' . $currentDate->format('H:i:sZ'),
                    'body' => $emailBody,
                    'button' => $emailButton
                ];
                /** Reset password process, notify user that his account is already registered */
                $this->getSlx()->getUserService()->resetPassword(
                    $requestRegister->getEmail(),
                    $userRegistered,
                    $parameters,
                    $emailSubject
                );
                /** Return success true */
                return $this->renderJson(['success' => true]);
            }

            /** If the user has successfully registered then send mail to activate account */
            if ($result['success'])
            {
                /** Notify the user with email */
               $this->getApp()->mailer->sendEmail([
                    'email' => $requestRegister->getEmail(),
                    'subject' => strip_tags($this->getTranslator()->trans('user.register.completed.email.subject')),
                    'emailText' => '',
                    'emailContent' => $this->renderView(
                        '@UserBundle/mail/account-created.html.twig',
                        ['user' => $requestRegister,'userAdded'=>$user,'source' => $source]
                    )
                ]);

                /** Return the userId to recuperate in the ajax request */
                return $this->renderJson(['success' => true, 'userId' => $result['userId']]);
            }
            return $this->renderJson(['success' => false, 'message' => 'Something went wrong please try again!']);
        }
        /** Return request registration view */
        return $this->render('@UserBundle/registration/request-register.html.twig',
            ['form' => $form->createView(),'source' => $source]);
    }


    /**
     * Complete registration when clicking in activation link
     * @param string $token
     * @return \Slim\Http\Response
     * @throws \Exception
     */
    public function completeRegistration(string $token) {
        /** Get the utm_source from the url */
        $source = '';
        $utm_source = $this->getApp()->request()->get('utm_source');
        if (isset($utm_source)) {
            $source = $utm_source;
        }
        /** Verify that the token exist & enable & time expiration are valid */
        $maxValidTime = new \DateTime();
        /** add the 6 hour expiration time */
        $maxValidTime->modify('-' . Config::read('tokenValidationHour') . ' hour');
        $maxValidTime->format('Y-m-d H:i:s');
        $em = $this->getEntityManager();
        /** find the request register by token */
        /** @var RequestRegister $requestRegister */
        $requestRegister = $em->getRepository(RequestRegister::class)->findValidToken($token, $maxValidTime);
        if (!$requestRegister)
        {
            return $this->render('@UserBundle/registration/registration-completed.html.twig', [
                'tokenExpired' => true,
                'newUser' => true
            ]);
        }
        /** @var UserService $userService */
        $userService = $this->getSlx()->getUserService();
        /** @var $slx $userService */
        $slx = $this->getSlx()->getSlxWebService();
        /** Fill user data from the registration request */
        $result = $slx->findUserByEmail($requestRegister->getEmail());
        if (isset($result['userId']))
        {
            /** Activate user account */
            $userService->activateUserAccount($requestRegister->getEmail());
            /** Authenticate user */
            $userService->authenticate($result['userId']);
            /** Redirect user to thank you page */
            $this->getSession()->set('registrationCompleted', true);

            $deviceCookies  = $this->getApp()->getCookie('user-device');
            //get old user-device
            $deviceDetails = $this->detectDevice();
            $newDeviceCookies = $deviceCookies?$deviceCookies.'u0'.$result['userId']:$deviceDetails.'u0'.$result['userId'];
            $this->getApp()->setcookie("user-device", $newDeviceCookies, time() + (86400 * 30), "/");

            //redirect to home page if registration completed
            if($this->getSession()->has('userInterest')) {
                $this->redirect(
                    $this->generateUrl('main')
                );
            }
            if ($source)
            {
                $this->redirect(
                    $this->generateUrl('registration-completed', ['lang' => $this->language, 'reg' => $this->region]).'?utm_source='.$source
                );
            } else
            {
                $this->redirect(
                    $this->generateUrl('registration-completed', ['lang' => $this->language, 'reg' => $this->region])
                );
            }

            return $this->renderJson(['success' => true, 'userId' => $result['userId']]);
        }
        return $this->renderJson(['success' => false, 'message' => '']);
    }

    /**
     * Complete registration thank you page
     * @throws \Exception
     */
    public function registrationCompleted()
    {
        /** Get the utm_source from the url */
        $source = '';
        $utm_source = $this->getApp()->request()->get('utm_source');
        if (isset($utm_source))
        {
            $source = $utm_source;
        }
        if($this->getUser()){
            $em = $this->getEntityManager();
            $user = $this->getUser();
            $existentUser = $em->getRepository(RequestRegister::class)->findOneBy(['email'=>$user->getEmail()]);
            if($existentUser && $existentUser->getMyIcGuide() == "first login" && !$this->getUser()->getHasInterests()){
                $this->getSession()->set('firstLogin', true);
                /** Set first login cookie true */
                $this->getApp()->setcookie("new-user", true);
                /** Redirect user to choose a favorite category */
                return $this->render('@UserBundle/registration/registration-completed.html.twig', ['source' => $source, 'newUser' => true]);
            }
        }
        /** if the user try access this page not after the registration is done then redirect to home page */
        if ($this->getUser() && $this->getUser()->getHasInterests())
        {
            /** Redirect to homepage */
            $this->redirect(
                $this->generateUrl('main')
            );
        } else
        {
            $newUser = false;
            if($this->getSession()->get('registrationCompleted')) {
                $newUser = true;
                $this->getSession()->set('firstLogin', true);
                /** Set first login cookie true */
                $this->getApp()->setcookie("new-user", true);
            }
            /** Remove registration complete from session */
            $this->getSession()->remove('registrationCompleted');

            return $this->render('@UserBundle/registration/registration-completed.html.twig', ['source' => $source, 'newUser' => $newUser]);
        }
        return true;
    }

    /**
     * @return \Slim\Http\Response|null
     * @throws \Exception
     */
    public function emailValidation(): ?\Slim\Http\Response
    {
        /** @var  $lang */
        $lang = $this->language;
        /** @var $slx $userService */
        $slx = $this->getSlx()->getSlxWebService();
        $restrictedEmails = $slx->getRestrictedEmails($lang, $this->region);
        /** Get email from request */
        $email = $this->getRequest()->get('email');
        list($user, $domain) = explode('@', $email);
        $contains = $this->arrayHaveDomain($restrictedEmails, $domain);
        if (count($contains) == 0)
        {
            return $this->renderJson(['success' => true, 'reply' => 'success']);
        } else
        {
            if ($lang === "de" || $this->region ===1)
            {
                return $this->renderJson(['success' => false, 'reply' => $this->trans('user.register.form.fields.email_popover')]);
            } else
            {
                return $this->renderJson(['success' => false, 'reply' => $this->trans('user.register.form.fields.email_tooltip')]);
            }
        }

    }

    /** Function that will check if the array contains the domain
     * @param $array
     * @param $str
     * @return array
     */
    public function arrayHaveDomain($array, $str)
    {
        /** This array will hold the indexes of every element that contains our substring. */
        $indexes = array();
        foreach ($array as $k => $v)
        {
            /** If stristr, add the index to our $indexes array */
            if (stristr($str, $v))
            {
                $indexes[] = $k;
                break;
            }
        }
        return $indexes;
    }

    /**
     * Check request registration attempts & validate recaptcha
     * @return bool
     * @throws \Exception
     */
    private function isRecaptchaValid()
    {
        /** Add the user attempts to the session */
        $session = $this->getSession();
        if (!$session->has('register_attempts'))
        {
            /** If this is the the first attempt then save it to the session */
            $session->set('register_attempts', [(new \DateTime())->getTimestamp()]);
        } else
        {
            /** @var array $attempts */
            $attempts = $session->get('register_attempts') ?? [];
            $currentAttemptTime = (new \DateTime())->getTimestamp();
            /** If this is the 4 attempts within 5 minute check if there is a recaptcha with the submit */
            if (count($attempts) == 3 && ($currentAttemptTime - $attempts[0] < 300))
            {
                $recaptcha = $this->getRequest()->request->get('g-recaptcha-response', null);
                if ($recaptcha && $this->recaptchaIsValid($recaptcha))
                {
                    /** Reset attempts number after the 4 attempt is done */
                    $attempts = [];
                } else
                {
                    return false;
                }
            } elseif (count($attempts) == 3)
            {
                array_shift($attempts);
                array_push($attempts, $currentAttemptTime);
            } else
            {
                array_push($attempts, $currentAttemptTime);
            }
            /** Save attempts in session */
            $session->set('register_attempts', $attempts);
        }
        return true;
    }

    /**
     * Check if recaptcha is valid
     * @param $recaptchaResponse
     * @return mixed
     * @throws \Exception
     */
    private function recaptchaIsValid($recaptchaResponse)
    {
        /** @var GoogleReCaptcha $googleRecaptchaService */
        $googleRecaptchaService = $this->getApp()->getService(GoogleReCaptcha::class);
        return $googleRecaptchaService->validate($recaptchaResponse);
    }
}
