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
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use UserBundle\Entity\ResetPasswordRequest;
use UserBundle\Entity\User;
use UserBundle\Form\ResetPasswordType;
use UserBundle\Form\ResettingFormType;

/**
 * Class ResettingController
 * @package controllers
 */
class ResettingController extends Controller
{
    /**
     * Reset password request throw ajax call
     * @throws \Exception
     */
    public function index()
    {
        $request = $this->getRequest();
        $email = $request->request->get('email');
        /** validate email */
        /** @var $violations */
        $violations = $this->validate($email, [new NotBlank(), new Email()]);
        $errors = [];
        $session = $this->getSession();
        if (0 === $violations->count())
        {
            /** check if the user submit the form with a recaptcha process in it or not */
            if (!$request->request->has('checkReCaptcha'))
            {
                if (!$session->has('fp_attempts'))
                {
                    /** if this is the the first attempt then save it to the session */
                    $session->set('fp_attempts', [(new \DateTime())->getTimestamp()]);
                } else
                {
                    /** @var array $attempts */
                    $attempts = $session->get('fp_attempts') ?? [];
                    $currentAttemptTime = (new \DateTime())->getTimestamp();
                    /** if this is the 4 attempts within 5 minute check if there is a recaptcha with the submit */
                    if ($this->isRecaptchaOn($attempts))
                    {
                        if ($this->getRequest()->request->has('g-recaptcha-response'))
                        {
                            $googleRecaptchaService = $this->getApp()->getService(GoogleReCaptcha::class);
                            $validRecaptcha = $googleRecaptchaService->validate(
                                $this->getRequest()->request->get('g-recaptcha-response', null)
                            );
                            if (!$validRecaptcha)
                            {
                                $this->renderJson([
                                    'result' => false,
                                    'recaptcha' => true,
                                    'message' => $this->trans('error.captcha'),
                                ]);
                            }
                        } else
                        {
                            $this->renderJson([
                                'result' => false,
                                'recaptcha' => true,
                                'message' => $this->trans('error.captcha'),
                                'attempts' => count($attempts),
                            ]);
                        }
                        $session->remove('fp_attempts');
                    } elseif (count($attempts) == 3)
                    {
                        array_shift($attempts);
                        array_push($attempts, $currentAttemptTime);
                        $session->set('fp_attempts', $attempts);
                    } else
                    {
                        array_push($attempts, $currentAttemptTime);
                        $session->set('fp_attempts', $attempts);
                    }
                }
            }

            /** check if the user exist */
            $user = $this->getSlx()->getSlxWebService()->findUserByEmail($email);
            if ($user)
            {
                $userInfo = $this->getSlx()->getUserService()->getUser($user['userId']);
                $emailBody = $this->trans('user.reset_password.email.found.body');
                /**
                 * array of parameters for view render
                 * @var array $parameters
                 */
                if ($this->language != 'de')
                {
                    $parameters = [
                        'body' =>
                            str_replace(
                                '__username__',
                                $userInfo->getFullName(),
                                $emailBody
                            ),

                    ];
                } else
                {
                    $emailBody = str_replace(
                        '__title__',
                        $userInfo->getTitle(),
                        $emailBody
                    );
                    $emailBody = str_replace(
                        '__lastname__',
                        $userInfo->getLastName(),
                        $emailBody
                    );
                    $parameters = [
                        'body' => $emailBody
                    ];
                }
                /**
                 * send reset password email
                 */
                $this->getSlx()->getUserService()->resetPassword(
                    $email,
                    $user,
                    $parameters,
                    $this->trans('user.reset_password.email.found.subject')
                );
            } else
            {
                /**
                 * send not Recognized user email
                 */
                $this->getApp()->mailer->sendEmail([
                    'email' => $email,
                    'subject' => $this->trans('user.reset_password.email.not_found.subject'),
                    'emailText' => '',
                    'emailContent' => $this->renderView('@UserBundle/mail/no-account.html.twig'),
                ]);
            }
        } else
        {
            /**
             * build errors messages to return json data errors
             */
            foreach ($violations as $violation)
            {
                $errors[] = $violation->getMessage();
            }
        }

        return $this->renderJson([
            'result' => count($errors) == 0,
            'message' => count($errors) == 0 ? $this->trans('email.sent') : $this->trans('error'),
            'recaptcha' => $this->isRecaptchaOn($session->get('fp_attempts') ?? []),
        ]);
    }

    /**
     * Reset user password
     * @param string $token
     * @return \Slim\Http\Response
     * @throws \Exception
     */
    public function reset(string $token)
    {
        $em = $this->getEntityManager();
        /**  verify that the token exist & enable & time expiration are valid */
        $maxValidTime = new \DateTime();
        /** add the 6 hour expiration time */
        $maxValidTime->modify('-' . Config::read('tokenValidationHour') . ' hour');
        $maxValidTime->format('Y-m-d H:i:s');
        /** @var ResetPasswordRequest $resetRequest */
        $resetRequest = $em->getRepository(ResetPasswordRequest::class)->findValidToken($token, $maxValidTime);
        $form = $this->createForm(ResettingFormType::class);
        $tokenValid = false;
        /** test if the request is valid */
        if ($resetRequest)
        {
            $request = $this->getRequest();
            if ($request->isMethod('GET'))
            {
                /** log operation */
                $this->getApp()->getMonoLog()->log('info', 'forgotten-password', 'forgottenPasswordLinkOpened', [
                    'processOutcome' => 'success',
                    'userId' => $resetRequest->getUserIdentifier(),
                ]);
            }

            $form->handleRequest($request);
            $tokenValid = true;
            if ($form->isSubmitted() && $form->isValid())
            {
                $userService = $this->getSlx()->getUserService();
                $userID = $resetRequest->getUserIdentifier();
                /** update the user data with the new password*/
                $response = $userService->updatePassword($userID, $form->getData()['password']);
                if ($response->Result)
                {
                    /** Remove the reset password token after the update is done */
                    $em->remove($resetRequest);
                    $em->flush();

                    $user = $userService->getUser($userID);
                    $this->notifyUser($user);
                    /** Log operation */
                    $this->getApp()->getMonoLog()->log('info', 'forgotten-password', 'orgottenPasswordChangePassword', [
                        'processOutcome' => 'success',
                        'userId' => $userID,
                    ]);
                    /** auto-login user after reset password */
                    $userService->authenticate($userID);

                    /** if the reset is a part of complete registration then redirect to profile */
                    if ($resetRequest->isRedirectProfile())
                    {
                        $this->redirect('profile-edit');
                    }
                } else
                {
                    $this->addFlash('error', $this->trans('error_') . $response->ErrorCode);
                    /** log operation */
                    $this->getApp()->getMonoLog()->log('info', 'forgotten-password', 'forgottenPasswordLinkOpened', [
                        'processOutcome' => 'failed',
                        'userId' => $userID,
                        'reason' => $this->trans('error_') . $response->ErrorCode
                    ]);
                }

                /** redirect to home page */
                $this->redirect(
                    $this->generateUrl('main')
                );
            }
        } else {
            /** log operation */
            $this->getApp()->getMonoLog()->log('info', 'forgotten-password', 'forgottenPasswordLinkOpened', [
                'processOutcome' => 'failed',
                'reason' => $this->trans('user.reset_password.expired_token')
            ]);
        }
        return $this->render(
            '@UserBundle/resetting/reset.html.twig',
            ['form' => $form->createView(), 'tokenValid' => $tokenValid]
        );
    }

    /**
     * Check if there is 3 attempt in 5 minute
     * @param array $attempts
     * @return bool|null
     * @throws \Exception
     */
    private function isRecaptchaOn(array $attempts): ?bool
    {
        $currentAttemptTime = (new \DateTime())->getTimestamp();
        return count($attempts) == 3 && ($currentAttemptTime - $attempts[0] < 300);
    }

    /**
     * Send email to the user to confirm that the password has changed
     * @param User $user
     * @throws \Exception
     */
    private function notifyUser(User $user)
    {
        /** @var \DateTime $currentDate */
        $currentDate = new \DateTime('now');
        $this->getApp()->mailer->sendEmail([
            'email' => $user->getEmail(),
            'subject' => $this->trans('user.reset_password.email.sucess.subject'),
            'emailText' => '',
            'emailContent' => $this->renderView(
                '@UserBundle/mail/confirm-reset-password.html.twig',
                [
                    'user' => $user,
                    'dateTime' => $currentDate->format('Y-m-d') . 'Z' . $currentDate->format('H:i:sZ')
                ]
            ),
        ]);
    }
}
