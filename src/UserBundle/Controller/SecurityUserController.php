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

use Doctrine\ORM\EntityManager;
use Exception;
use MBComponents\Controller\Controller;
use MBComponents\Helpers\Encryption;
use MBComponents\Helpers\MainHelper;
use MBComponents\HttpFoundation\Session;
use MBComponents\Monolog\Monolog;
use Slim\Http\Response;
use UserBundle\Entity\Code;
use UserBundle\Entity\RequestRegister;
use UserBundle\Entity\User;

/**
 * Class SecurityUserController
 * @package UserBundle\Controller
 */
class SecurityUserController extends Controller
{
    /**
     * Login from ajax call
     * @throws Exception
     */
    public function login(): ?Response
    {
        $request = $this->getRequest();
        $userService = $this->getSlx()->getUserService();
        $result = $userService->userLogin($request->get('username'), $request->get('password'));
        /** Login success */
        if ($result['success'])
        {
            /** Check the logged user version */
            /** @var User $user */
            $user = $userService->getUser($request->get('username'));
            $em = $this->getEntityManager();

            $this->getSession()->set('Email', $request->get('username'));
            $this->getSession()->set('Userid', $result['userId']);

            /** authenticate user */
            $userService->authenticate($request->get('username'));
            //verify user identity when we have new user agent
            $deviceCookies = $this->getApp()->getCookie('user-device');
            $tabCookies = array();
            $host = '';
            if($deviceCookies) {
                $tabCookies = explode('u0',$deviceCookies);
                $host = $tabCookies[0];
            }
            $deviceDetails = $this->detectDevice();
            //if user don't have device cookies generate a random code and send it by mail to confirm user identity
            if (!$deviceCookies || $host !== $deviceDetails || (!in_array($user->getUserId(), $tabCookies)))
            {
                $userCode = $em->getRepository(Code::class)->findOneBy(['email' =>$request->get('username')]);

                if ( $userCode == null) {
                    $userCode = new Code();
                    $userCode->setEmail($request->get('username'));
                }

                $userCode->setCode(random_int(100000, 999999));
                $em->persist($userCode);

                /** @var Code $userCode */
                $em->flush();
                /** send email with code */
                $this->verifyWithCode($userCode);
            }

            /** remember me  */
            if ($request->get('remember') === 'on')
            {
                $serializedToken = Encryption::encrypt($result['userId']);
                setcookie(
                    'rem_user',
                    $serializedToken,
                    strtotime('+30 days'),
                    '/',
                    $this->getRequest()->server->get("SERVER_NAME"),
                    0
                );
            } else {
                setcookie('rem_user', 0, time() + 1200, '/', $this->getRequest()->server->get("SERVER_NAME"), 0);
            }
            setcookie(
                'site-lang',
                $this->getApp()->session->get('lang'),
                "",
                "",
                "",
                true,
                true
            );
            $defaultSwitcher = '/'.$this->getApp()->session->get('region').'/'.$this->getApp()->session->get('lang');
            /** set the site switcher value to be the empty */
            setcookie("switch-catalog", $defaultSwitcher, time() + (86400 * 30), "/","",true, true);
            //get old user-device
            $newDeviceCookies = $deviceCookies?$deviceCookies.'u0'.$user->getUserId():$deviceDetails.'u0'.$user->getUserId();
            setcookie("user-device", $newDeviceCookies, time() + (86400 * 30), "/");
            /** Check the logged user version */
            $em = $this->getEntityManager();
            $existentUser = $em->getRepository(RequestRegister::class)->findOneBy(['email' => $user->getEmail()]);
            if ($user->geticProfileVersion() !== null && $user->geticProfileVersion() !== "0" && $existentUser === null)
            {
                $existentUser = new RequestRegister();
                $existentUser->setFirstName($user->getFirstName());
                $existentUser->setLastName($user->getLastName());
                $existentUser->setPassword('user');
                $existentUser->setEmail($user->getEmail());
                $em->persist($existentUser);
                $em->flush();
            }
            //if (!$deviceCookies || $host !== $deviceDetails || (!in_array($user->getUserId(), $tabCookies))) {
                /** redirect to confirm authentication after loggedIn  when user don't have device cookies (expired or first login) or when user device cookies is updated*/
               // return $this->renderJson(['success' => true, 'redirect' => $this->generateUrl('confirm-authentication')]);
           // } else {
                return $this->renderJson(['success' => true, 'redirect' => $userService->getRedirectUrl()]);
           // }

        }

        /** Login fail */
        if ($result['ErrorCode'] === '1105')
        {
            return $this->renderJson(['success' => false, 'reply' => $result['ErrorCode']]);
        }

        $data = ['processOutcome' => 'failed',];
        $user = $userService->getUser($request->get('username'));
        if ($user)
        {
            $data['userId'] = $user->getUserId();
        }
        /** log operation */
        /** @var Monolog $monolog */
        $monolog = $this->getApp()->getService(Monolog::class);
        $monolog->log('info', 'login', 'userLogon', $data);
        return $this->renderJson(['success' => false]);
    }

    /**
     * Logout
     * @throws Exception
     */
    public function logout(): void
    {
        /** delete remember me from cookie */
        setcookie("rem_user", "", time() - 3600, '/', $this->getRequest()->server->get("SERVER_NAME"), 0);
        /** set the site lang value to be the initial value */
        $defaultLang = $this->getApp()->session->get('default-lang');
        setcookie("site-lang", $defaultLang, time() + (86400 * 30), "/");
        /** set the site switcher catalog value to be the empty */
        setcookie("switch-catalog", '', time() + (86400 * 30), "/","",true,true);
        $userService = $this->getSlx()->getUserService();
        /** Remove user from session */
        $userService->removeUserFromSession();
        $this->getSession()->remove('Email');
        $this->getSession()->remove('Userid');
        $this->getSession()->remove('firstLogin');
        $this->getSession()->remove('fileUploaded');
        /** redirect to home page after loggedOut */
        $this->redirect($this->generateUrl('main'));
    }

    /**
     * Confirm authentication page
     * @throws \Exception
     */
    public function confirmAuthentication()
    {
        if ( $this->getRequest()->get('code') != null) {
            $code = $this->getRequest()->get('code');
            /** @var EntityManager $em */
            $em = $this->getEntityManager();
            /** @var Code $codeSent */
            $codeSent = $em->getRepository(Code::class)->findOneBy(['email' => $this->getSession()->get('Email')]);

            /** need to authenticate user to get email address  */
            if ( $codeSent != null && $code == $codeSent->getCode()){
                $em->remove($codeSent);
                $em->flush();
                $userService = $this->getSlx()->getUserService();
                $userService->authenticate($this->getSession()->get('Email'));
                /** redirect to home page after log in */
                return $this->renderJson(['success'=>true,'redirect'=>$this->generateUrl('main')]);
            } else {
                return $this->renderJson(['success'=>false,'message'=>'Code incorrect']);
            }
        }

        return $this->render('@UserBundle/security/confirm-authentication.html.twig');
    }
    /**
     * Resend code
     * @throws \Exception
     */
    public function resendCode()
    {
        $em = $this->getEntityManager();
        /** @var Code $userCode */
        $userCode = $em->getRepository(Code::class)->findOneBy(['email' => $this->getSession()->get('Email')]);
        if ( $userCode != null) {
            $userCode->setCode(random_int(100000, 999999));
        } else {
            $userCode = new Code();
            $userCode->setEmail($this->getSession()->get('Email'));
        }
        $em->persist($userCode);
        $em->flush();
        /** send email with code */
        $this->verifyWithCode($userCode);
        return    $this->renderJson(['success'=>true,'message'=>'Code sent']);
    }

    /**
     * @param Code $userCode
     * @throws Exception
     */
    public function verifyWithCode($userCode){
        /** send email with code and user details*/
        $user = $this->getUser();

        if($user) {
            $ipAddress   = MainHelper::getClientIpAddress($this->getRequest()->server->all());
            $countryInfo =  $this->getIpInfo($ipAddress);
            $countryName = isset($countryInfo['countryName'])?$countryInfo['countryName']:$this->getSession()->get('countryCode-'.$ipAddress);
            $emailParams = [
                'subject'=> $this->trans('email.subject'),
                'email' => $user->getEmail(),
                'emailContent' => $this->renderView(
                    '@UserBundle/mail/confirm-authentication.html.twig',
                    [
                        'userName' => $user->getTitle().' '.$user->getFirstName(). ' '.$user->getLastName(),
                        'code' => $userCode->getCode(),
                        'country' => $countryName,
                        'date' => date("d-m-Y h:i"),
                        'device' => $this->detectDevice()
                    ]
                )];
           // $this->getApp()->mailer->sendEmail($emailParams);
        }
    }
}