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

namespace UserBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use lib\Config;
use MBComponents\Helpers\Mailer;
use MBComponents\HttpFoundation\Session;
use MBComponents\Monolog\Monolog;
use MBComponents\Services\AppService;
use MBComponents\Services\SlxWebService;
use MBComponents\Twig\TwigEnvironment;
use Slim\Views\Twig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validation;
use UserBundle\Entity\Address;
use UserBundle\Entity\Institution;
use UserBundle\Entity\ResetPasswordRequest;
use UserBundle\Entity\User;

/**
 * Class UserService
 * @package UserBundle\Services
 */
class UserService extends SlxWebService
{
    /** @var User */
    private $user;

    /** @var string */
    private $redirectUrl;

    /**
     * UserService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->user = $this->getUserFromSession();
    }

    /**
     * Get a user from slx or session by email or user id
     * @param string $finder
     * @return User
     */
    public function getUser(string $finder = ""): User
    {
        if ($this->user !== null)
        {
            return $this->user;
        }
        $validator = Validation::createValidator();
        $violations = $validator->validate($finder, [new Email()]);
        $this->user = new User();
        /** Find user by email from salesLogix */
        if (0 === $violations->count())
        {
            /** Get user id from slx by his email */
            $response = $this->getSoap(
                'elsGet_UserID',
                ['idsite' => Config::read('currentSiteId'), 'email' => $finder]
            );
            $this->user->setCreateUser($response->elsGet_UserIDResult->CreateUser);
            $this->user->setUserId($response->elsGet_UserIDResult->Reply);
            /** Get user info by his id */
            $this->getUserFromIdentifier($this->user->getUserId());

        } else
            {
            $this->user->setUserId($finder);
            $this->getUserFromIdentifier($finder);
        }

        return $this->user;
    }

    /**
     * Remove user from session
     */
    public function removeUserFromSession(): void
    {
        /** @var Session $session */
        $session = $this->container->get(Session::class);
        $session->remove('userInfo');
        $session->remove('user');
        /** Remove user's education consultant information */
        $session->remove('AMemail');
        $session->remove('AMName');
        $session->remove('AMPhone');
        $session->remove('UserCountry');
    }

    /**
     * Get user information from slx by his id
     * @param $finder
     */
    public function getUserFromIdentifier(string $finder): void
    {
        $response = $this->getSoap('elsGet_User', ['idsite' => Config::read('currentSiteId'), 'userid' => $finder]);
        if ($response->elsGet_UserResult->Result)
        {
            /** initialize the version value to 0 to avoid errors if the key is not returned by wp */
            $this->user->setIcProfileVersion('0');
            if (isset($response->elsGet_UserResult->ProfileFiles))
            {
                if(isset($response->elsGet_UserResult->ProfileFiles->ProfileFile)) {
                    $this->user->setProfileFile((array)$response->elsGet_UserResult->ProfileFiles->ProfileFile);
                }
            }
            foreach ($response->elsGet_UserResult as $key => $val)
            {
                $setter = 'set' . $key;
                if (method_exists($this->user, $setter))
                {
                    $this->user->$setter($val);
                }
            }
            //only for test remove this line after validate tests
            //$this->user->setIcProfileVersion('5');
            /** Get user institutions */
            $this->user->setInstitutions($this->getUserInstitutions($finder));
            /** Get user addresses */
            $this->user->setAddresses($this->getUserAddresses($finder));
            $this->saveUserIntoSession($this->user);
        }
    }

    /**
     * Authenticate user & save his information to the session
     * @param $finder
     */
    public function authenticate(string $finder)
    {
        $loggedInUser = $this->getUser($finder);
        /** @var Session $session */
        $session = $this->container->get(Session::class);

        if (null === $loggedInUser->getUserId())
        {
            return;
        }
        /** Save Education consultant information into the session */
        $session->set('AMemail', $loggedInUser->getAmEmail());
        $session->set('AMName', $loggedInUser->getAmName());
        $session->set('AMPhone', $loggedInUser->getAmPhone());
        $session->set('UserCountry', $loggedInUser->getCountry());

        /** Save user to session */
        $this->saveUserIntoSession($loggedInUser);
        $session->set('userInfo', [
            'Userid' => $loggedInUser->getUserId(),
            'Firstname' => $loggedInUser->getFirstName(),
            'Lastname' => $loggedInUser->getLastName(),
            'Email' => $loggedInUser->getEmail()
        ]);
        $region = $session->get('region');
        $lang = $session->get('lang');

        if (!empty($loggedInUser->getCountry()))
        {
            $data = $this->getLanguageRegionFromCountry($this->getUser()->getCountry());
            $region = $data['region'];
            $lang = $data['language'];
            /** Update session lang & region */
            $session->set('region', $region);
            $session->set('lang', $lang);
        }
        /** @var TwigEnvironment $twig */
        $twig = $this->container->get(TwigEnvironment::class);
        /** Update baseUrl */
        $this->redirectUrl = $twig->updateBaseUrl([$region, $lang]);
    }

    /**
     * Save user into session
     * @param User $user
     */
    public function saveUserIntoSession(User $user)
    {
        /** @var Session $session */
        $session = $this->container->get(Session::class);
        $session->set('user', serialize($user));
    }

    /**
     * Get user from session
     * @return User
     */
    private function getUserFromSession(): ?User
    {
        /** @var Session $session */
        $session = $this->container->get(Session::class);
        $userSession = null;
        /** if user exist in session then deserialize his data */
        if ($session->has('user'))
        {
            $userSession = unserialize($session->get('user'));
        }
        return $userSession;
    }

    /**
     * Get user country code region
     * @param $user
     * @param string $lang
     * @return string
     */
    public function getUserCodeRegion($user, string $lang = 'en'): string
    {
        /** get country from User object */
        if (!$user instanceof User)
        {
            /** @var User $user */
            $user = $this->getUser($user);
        }
        /** @var string $country */
        $country = $user->getCountry();
        return $this->getRegionByCountry($country, $lang);
    }

    public function getUserCountryCode($country)
    {
        return $this->getCountryIso($country);
    }

    /**
     * Reset user password and send email
     * @param $email
     * @param $identifier
     * @param $parameters
     * @param string $emailSubject
     * @param bool $redirectProfile
     */
    public function resetPassword(
        string $email,
        array $identifier,
        array $parameters,
        string $emailSubject = '',
        bool $redirectProfile = false
    )
    {
        /** @var EntityManager $em */
        $entityManager = $this->container->get(EntityManager::class);
        /** @var ResetPasswordRequest $request */
        $request = new ResetPasswordRequest($email);
        $request->setRedirectProfile($redirectProfile);
        /** update request status & set the founded user to request object */
        $request->setUserIdentifier($identifier['userId']);
        $request->setEnabled(true);
        /** add the request data to DB */
        $entityManager->persist($request);

        /** delete old request for the current user */
        $entityManager->getRepository(ResetPasswordRequest::class)->createQueryBuilder('e')
            ->delete()
            ->where('e.email = :email')
            ->setParameter('email', $email)
            ->getQuery()->execute();

        $parameters['request'] = $request;
        /** @var Mailer $mailer */
        $mailer = $this->container->get(Mailer::class);
        /** @var Twig $twig */
        $twig = $this->container->get(TwigEnvironment::class);
        /** @var Monolog $monolog */
        $monolog = $this->container->get(Monolog::class);

        /** send reset password email */
        $mailer->sendEmail([
            'email' => $email,
            'subject' => $emailSubject,
            'emailText' => '',
            'emailContent' => $twig->render('@UserBundle/mail/reset-password.html.twig', $parameters),
        ]);
        $entityManager->flush();

        /** log operation */
        $monolog->log('info', 'forgotten-password', 'forgottenPasswordInititated', [
            'processOutcome' => 'success',
            'userId' => $identifier['userId']
        ]);
    }

    /**
     * Add user to slx
     * @param User $user
     * @return array
     */
    public function addUser(User $user): array
    {
        /**  Call web service to insert new user to salesLogix */
        $response = $this->getSoap('elsSet_User', [
            'idsite' => Config::read('currentSiteId'),
            'userid' => '',
            /** build user data from post request */
            'userdata' => [
                'Email' => $user->getEmail(),
                'Password' => $user->getPassword(),
                'Country' => trim($user->getCountry()),
                'Title' => $user->getTitle(),
                'Firstname' => $user->getFirstname(),
                'Middlename' => $user->getMiddlename(),
                'Lastname' => $user->getLastname(),
                'Website' => $user->getUrl(),
                'vatNumber' => $user->getCpf(),
                'DoNotSolicit' => $user->isAcceptMarketing()
            ]
        ]);
        if ($response->elsSet_UserResult->Result)
        {
            return ['success' => true, 'userId' => $response->elsSet_UserResult->Reply];
        } else
            {
            return [
                'success' => false,
                'code' => 'error_' . $response->elsSet_UserResult->ErrorCode ?? '',
                'message' => $response->elsSet_UserResult->Reply
            ];
        }
    }

    /**
     * @param User $user
     * @return array
     */
    public function updateUser(User $user): array
    {
        /**  Call web service to insert new user to salesLogix */
        $response = $this->getSoap('elsSet_User', array(
            'idsite' => Config::read('currentSiteId'),
            'userid' => $user->getUserId(),

            /** build user data from post request */
            'userdata' => [
                'Userid' => $user->getUserId(),
                'Title' => $user->getTitle(),
                'Firstname' => $user->getFirstname(),
                'Middlename' => $user->getMiddlename(),
                'Lastname' => $user->getLastname(),
                'Mobile' => $user->getMobile(),
                'Mainphone' => $user->getMainPhone(),
                'Country' => $user->getCountry(),
                'Email' => $user->getEmail(),
                'Website' => $user->getUrl(),
                'VatNumber' => $user->getCpf(),
                'Institutionid' => $user->getInstitutionId(),
                'InstitutionName' => $user->getInstitutionName(),
                'Departmentid' => $user->getDepartmentId(),
                'DepartmentName' => $user->getDepartmentName(),
                'Profession' => $user->getProfession(),
                'Specialty' => $user->getSpecialty(),
                //'IcProfileVersion' => "0",
                // important not update password from this webservice
                'Password' => '',
            ]), false);
        if ($response->elsSet_UserResult->Result)
        {
            return ['success' => true, 'userId' => $response->elsSet_UserResult->Reply];
        } else
            {
            return [
                'success' => false,
                'code' => 'error_' . $response->elsSet_UserResult->ErrorCode,
                'message' => $response->elsSet_UserResult->Reply
            ];
        }
    }

    /**
     * @param $userId
     * @param $fileName
     * @param $file
     * @return array
     */
    public function uploadFile($userId, $fileName, $file)
    {

        /** Get file content from the tmp_name */
        $source = file_get_contents($file['tmp_name']);
        /** Decode content file to base 64 */
        $content = base64_encode($source);
        /**  Call web service to upload a file to salesLogix */
        $response = $this->getSoap('elsSet_UserUploadFile', [
            'idsite' => Config::read('currentSiteId'),
            'userid' => $userId,
            'f' => $content,
            'fileName' => $fileName
        ]);
        if ($response->elsSet_UserUploadFileResult->Result)
        {
            return ['success' => true];
        } else
            {
            return [
                'success' => false,
                'code' => 'error_' . $response->elsSet_UserUploadFileResult->ErrorCode,
                'message' => $response->elsSet_UserUploadFileResult->Reply
            ];
        }
    }

    /**
     * Insert or add user to SLX
     * @param array $data
     * @param bool $notify
     * @return array
     * @throws \Exception
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function setUser(array $data, bool $notify = false): array
    {
        /** call all needed services */
        /** @var Session $session */
        $session = $this->container->get(Session::class);
        /** @var Mailer $mailer */
        $mailer = $this->container->get(Mailer::class);
        /** @var Translator $translator */
        $translator = $this->container->get(Translator::class);
        /** @var TwigEnvironment $twig */
        $twig = $this->container->get(TwigEnvironment::class);
        /** @var AppService $appService */
        $appService = $this->container->get(AppService::class);
        /**
         * @var array $userData
         * init user data
         */
        $userData = [
            'title' => '',
            'lastName' => '',
            'middleName' => '',
            'firstName' => '',
            'mainPhone' => '',
            'profession' => '',
            'specialty' => '',
            'password' => '',
            'institutionId' => '',
            'institutionName' => '',
            'departmentId' => '',
            'departmentName' => '',
            'address1' => '',
            'address2' => '',
            'address3' => '',
            'address4' => '',
            'city' => '',
            'state' => '',
            'stage' => '',
            'postalcode' => '',
            'email' => '',
            'lang' => '',
            'code_region' => '',
            'suffix' => '',
            'donotsolicit' => false,
            'subject' => '',
            'vatNumber' => '',
            'country' => '',
            'salutation' => '',
            'program' => '',
            'course' => '',
            'userId' => '',
            'mobile' => '',
            'website' => '',
        ];
        /**
         * define acceptMarketing input checkbox with true if it's not existing
         */
        if (!isset($data['acceptMarketing']))
        {
            $data['acceptMarketing'] = false;
        }

        foreach ($data as $key => $value)
        {
            $userData[$key] = $value;
        }
        /**
         * Call web service to insert user to salesLogix
         */
        $response = $this->getSoap('elsSet_User', array(
            'idsite' => Config::read('currentSiteId'),
            'userid' => $userData['userId'],
            /** build user data from post request */
            'userdata' => [
                /** right inputs */
                'DepartmentName' => $userData['departmentName'],
                'Departmentid' => $userData['departmentId'],
                'InstitutionName' => $userData['institutionName'],
                'Institutionid' => $userData['institutionId'],
                'State' => $userData['state'],
                'City' => $userData['city'],
                'Postalcode' => $userData['postalcode'],
                'Address4' => $userData['address4'],
                'Address3' => $userData['address3'],
                'Address2' => $userData['address2'],
                'Address1' => $userData['address1'],
                'Country' => $userData['country'],
                'DoNotSolicit' => !$userData['acceptMarketing'],
                /** left inputs */
                'Title' => $userData['title'],
                'Lastname' => $userData['lastName'],
                'Middlename' => $userData['middleName'],
                'Firstname' => $userData['firstName'],
                'Specialty' => $userData['specialty'],
                'Profession' => $userData['profession'],
                'Password' => $userData['password'],
                'Email' => $userData['email'],
                'Mobile' => $userData['mobile'],
                'Mainphone' => $userData['mainPhone'],
                'VATNumber' => $userData['vatNumber'],
                'Website' => $userData['website'],
                /** others inputs not defined yet */
                'Stage' => $userData['stage'],
                'Course' => $userData['course'],
                'Program' => $userData['program'],
                'Suffix' => $userData['suffix'],
                'Salutation' => $userData['salutation'],
                'Userid' => '',
            ]), false);

        /** @var array $output */
        $output['success'] = $response->elsSet_UserResult->Result;
        /** update profile process */
        if ($userData['userId'] !== '')
        {
            /** user updated to salesLogix */
            $region = $this->getUserCodeRegion($userData['userId'], $session->get('lang'));
            $session->set('region', $region);
            $output['redirect'] = $appService->generateUrl('account', [
                'lang' => $session->get('lang'),
                'reg' => $region
            ]);
        } else
            {
            if ($response->elsSet_UserResult->Result)
            {
                $this->authenticate($response->elsSet_UserResult->Reply);

                /** user inserted to salesLogix */
                $session->set('registrationCompleted', true);

                /** redirect to home */
                $this->getSoap(
                    'elsSet_Activate_User',
                    ['idsite' => Config::read('currentSiteId'),
                        'email' => $userData['email']]
                );
                if ($notify)
                {
                    /** notify the user with email */
                    $mailer->sendEmail([
                        'email' => $userData['email'],
                        'subject' => $translator->trans('user.register.completed.email.subject'),
                        'emailText' => '',
                        'emailContent' => $twig->renderView('@UserBundle/mail/account-created.html.twig', [
                            'redirect' => $this->redirectUrl,
                            'success' => $output['success']
                        ]),
                    ]);
                }
            } else
                {
                $output['code'] = 'error_' . $response->elsSet_UserResult->ErrorCode;
                $output['message'] = $response->elsSet_UserResult->Reply;
                if ($response->elsSet_UserResult->ErrorCode == 'error_1085')
                {
                    /** Reset password process, notify user that his account is already registered */
                    $this->resetPassword(
                        $userData['email'],
                        $userData,
                        ['body' => $translator->trans('user.reset_password.email.found.body')],
                        $translator->trans('user.reset_password.email.found.subject')
                    );
                }
            }
        }

        return $output;
    }

    /**
     * Activate user account
     * @param $email
     */
    public function activateUserAccount($email): void
    {
        $this->getSoap('elsSet_Activate_User', ['idsite' => Config::read('currentSiteId'), 'email' => $email]);
    }

    /**
     * Update user's password with salesLogic
     * @param $userID
     * @param $newPassword
     * @return object
     */
    public function updatePassword(string $userID, string $newPassword): object
    {
        $response = $this->getSoap(
            'elsSet_Password',
            ['idsite' => Config::read('currentSiteId'), 'userid' => $userID, 'newpass' => $newPassword]
        );
        return $response->elsSet_PasswordResult;
    }

    /**
     * Return redirection url
     * @return string
     */
    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }

    /**
     * Check user login to slx and return user id
     * @param string $email
     * @param string $password
     * @return array
     */
    public function userLogin(string $email, string $password): array
    {
        $response = $this->getSoap(
            'elsGet_UserLogin',
            ['idsite' => Config::read('currentSiteId'), 'email' => $email, 'password' => $password]
        );
        /** verify if the user is logged in via slx */
        if ($response->elsGet_UserLoginResult->Result)
        {
            /** return user id */
            return ['success' => true, 'userId' => $response->elsGet_UserLoginResult->Reply];
        } else
            {
            return ['success' => false, 'ErrorCode' => $response->elsGet_UserLoginResult->ErrorCode ?? ''];
        }
    }

    /**
     * Add or update a user address from slx
     * @param $userID
     * @param Address $address
     * @return array
     */
    public function addOrUpdateUserAddress($userID, Address $address): array
    {
        $response = $this->getSoap(
            'elsSet_User_Address',
            array(
                'idsite' => Config::read('currentSiteId'),
                /** build user data from post request */
                'addr' => [
                    'Userid' => $userID,
                    'CanBeDeleted' => $address->getCanBeDeleted() ? 1 : 0,
                    'Addressid' => $address->getId(),
                    'Address1' => $address->getAddress1(),
                    'Address2' => $address->getAddress2(),
                    'Address3' => $address->getAddress3(),
                    'Address4' => $address->getAddress4(),
                    'Postalcode' => $address->getPostalCode(),
                    'City' => $address->getCity(),
                    'Country' => $address->getCountry(),
                    'Primary' => $address->isPrimary() ? 1 : 0,
                    'State' => $address->getState(),
                    'Description' => '',
                    'Salutation' => '',
                    'Type' => '',
                    'Mainphone' => $address->getPhone(),
                ])
        );
        if ($response->elsSet_User_AddressResult->Resultado)
        {
            return ['success' => true, 'reply' => $response->elsSet_User_AddressResult->Respuesta];
        } else
            {
            return [
                'success' => false,
                'code' => 'error_' . $response->elsSet_User_AddressResult->Codigoerror,
                'message' => $response->elsSet_User_AddressResult->Respuesta
            ];
        }
    }

    /**
     * Delete user address
     * @param $userId
     * @param $addressId
     * @return array
     */
    public function deleteUserAddress($userId, $addressId)
    {
        $response = $this->getSoap(
            'elsDel_User_Address',
            [
                'idsite' => Config::read('currentSiteId'),
                'userid' => $userId,
                'addressid' => $addressId,
            ]
        );
        return [
            'success' => $response->elsDel_User_AddressResult->Resultado,
            'reply' => $response->elsDel_User_AddressResult->Respuesta
        ];
    }


    /**
     * Disable user address with a slx call
     * @param $userId
     * @param $addressId
     * @param $isDisabled
     * @return array
     */
    public function disableUserAddress($userId, $addressId, $isDisabled)
    {
        $params = [
            'idsite' => Config::read('currentSiteId'),
            'userid' => $userId,
            'addressid' => $addressId,
            'isdisabled' => $isDisabled,
        ];
        $response = $this->getSoap('elsDisable_User_Address', $params);

        return [
            'success' => $response->elsDisable_User_AddressResult->Result,
            'reply' => $response->elsDisable_User_AddressResult->Reply
        ];
    }

    /**
     * Return user addresses from slx
     * @param $userId
     * @return ArrayCollection
     */
    public function getUserAddresses($userId): ArrayCollection
    {
        /** Call user get address ws by his id */
        $response = $this->getSoap(
            'elsGet_User_Addresses',
            array(
                'idsite' => Config::read('currentSiteId'),
                'userid' => $userId,
            )
        );
        /** Create an addresses collection */
        $userAddresses = new ArrayCollection();
        if ($response->elsGet_User_AddressesResult->Resultado)
        {
            $addresses = [];
            /** Check if the webservice return a single or multiple addresses */
            if (isset($response->elsGet_User_AddressesResult->Lista->Address))
            {
                if (!is_array($response->elsGet_User_AddressesResult->Lista->Address))
                {
                    $addresses[] = $response->elsGet_User_AddressesResult->Lista->Address;
                } else
                    {
                    $addresses = $response->elsGet_User_AddressesResult->Lista->Address;
                }
            }
            /** Parse each slx address info into Address object and add them to the address collection*/
            foreach ($addresses as $add)
            {
                $address = new Address();
                $address->setId($add->Addressid);
                $address->setAddress1($add->Address1);
                $address->setAddress2($add->Address2);
                $address->setAddress3($add->Address3);
                $address->setAddress4($add->Address4);
                $address->setPostalCode($add->Postalcode);
                $address->setCity($add->City);
                $address->setCountry($add->Country);
                $address->setDescription($add->Description);
                $address->setIsPrimary($add->Primary);
                $address->setState($add->State);
                $address->setSalutation($add->Salutation);
                $address->setCanBeDeleted($add->CanBeDeleted);
                $address->setType($add->Type);
                $address->setPhone($add->Mainphone);
                $userAddresses->add($address);
            }
        }
        return $userAddresses;
    }

    /**
     * Add institution web service
     * @param $userID
     * @param Institution $institution
     * @return array
     */
    public function addUserInstitution($userID, Institution $institution): array
    {
        /** Call get user institutions webservice */
        $response = $this->getSoap(
            'elsSet_UserInstitution',
            [
                'idsite' => Config::read('currentSiteId'),
                'userid' => $userID,
                'userinstitutionid' => "",
                'departmentid' => $institution->getDepartmentId(),
                'department' => $institution->getDepartmentName(),
                'institutionid' => $institution->getInstitutionId(),
                'institution' => $institution->getInstitutionName(),
                'specialty' => $institution->getSpeciality(),
                'profession' => $institution->getProfession(),
                'course' => '',
                'isPrimary' => $institution->isPrimary(),
                'status' => '1'
            ]
        );
        return [
            'success' => $response->elsSet_UserInstitutionResult->Result,
            'reply' => $response->elsSet_UserInstitutionResult->Reply
        ];
    }

    /**
     * Update institution web service
     * @param $userID
     * @param Institution $userInstitution
     * @return array
     */
    public function updateUserInstitution($userID, Institution $userInstitution): array
    {
        /** Call set user institution by it's id to update it */
        $response = $this->getSoap(
            'elsSet_UserInstitution',
            [
                'idsite' => Config::read('currentSiteId'),
                'userid' => $userID,
                'userinstitutionid' => $userInstitution->getId(),
                'departmentid' => $userInstitution->getDepartmentId(),
                'institution' => $userInstitution->getInstitutionName(),
                'institutionid' => $userInstitution->getInstitutionId(),
                'department' => $userInstitution->getDepartmentName(),
                'specialty' => $userInstitution->getSpeciality(),
                'profession' => $userInstitution->getProfession(),
                'course' => '',
                'isPrimary' => $userInstitution->isPrimary(),
                'status' => $userInstitution->isEnabled() ? '1' : '0',
            ]
        );
        return [
            'success' => $response->elsSet_UserInstitutionResult->Result,
            'reply' => $response->elsSet_UserInstitutionResult->Reply
        ];
    }

    /**
     * Delete user institution from slx
     * @param $userId
     * @param $userInstitutionId
     * @return array
     */
    public function deleteUserInstitution($userId, $userInstitutionId)
    {
        $response = $this->getSoap(
            'elsDel_UserInstitution',
            [
                'idsite' => Config::read('currentSiteId'),
                'userid' => $userId,
                'userinstitutionid' => $userInstitutionId,
            ]
        );
        return [
            'success' => $response->elsDel_UserInstitutionResult->Result,
            'reply' => $response->elsDel_UserInstitutionResult->Reply
        ];
    }

    /**
     * Return user institutions from slx
     * @param $userId
     * @return ArrayCollection
     */
    public function getUserInstitutions($userId): ArrayCollection
    {
        /** Call the ws with user'id */
        $response = $this->getSoap(
            'elsGet_UserInstitutions',
            array(
                'idsite' => Config::read('currentSiteId'),
                'userid' => $userId,
            )
        );
        /** Create an institutions collection */
        $userInstitutions = new ArrayCollection();
        if ($response->elsGet_UserInstitutionsResult->Result)
        {
            $institutions = [];
            /** Check if the webservice return a single or multiple institutions */
            if (isset($response->elsGet_UserInstitutionsResult->InstitutionsList->WSUserInstitution))
            {
                if (!is_array($response->elsGet_UserInstitutionsResult->InstitutionsList->WSUserInstitution))
                {
                    $institutions[] = $response->elsGet_UserInstitutionsResult->InstitutionsList->WSUserInstitution;
                } else
                    {
                    $institutions = $response->elsGet_UserInstitutionsResult->InstitutionsList->WSUserInstitution;
                }
            }
            /** Parse each slx institution info into Institution object and add them to the institutions collection*/
            foreach ($institutions as $inst)
            {
                $institution = new Institution();
                $institution->setId($inst->Userinstitutionid);
                $institution->setInstitutionId($inst->Institutionid);
                $institution->setInstitutionName($inst->Institution);
                $institution->setDepartmentId($inst->Departmentid);
                $institution->setDepartmentName($inst->Department);
                $institution->setProfession($inst->Profession);
                $institution->setSpeciality($inst->Specialty);
                $institution->setIsPrimary($inst->IsPrimary);
                $institution->setEnabled($inst->Status ?? false);
                $institution->setHasRequest(isset($inst->Copiesno) && $inst->Copiesno > 0);
                $userInstitutions->add($institution);
            }
        }
        return $userInstitutions;
    }

    /**
     * @param User $user
     * @param string $interests
     * @return array
     */
    public function updateUserInterests(User $user, $interests): array
    {
        /**  Call web service to insert new user to salesLogix */
        $response = $this->getSoap('elsSet_User', array(
            'idsite' => Config::read('currentSiteId'),
            'userid' => $user->getUserId(),

            /** build user data from post request */
            'userdata' => [
                'Userid' => $user->getUserId(),
                'Title' => $user->getTitle(),
                'Firstname' => $user->getFirstname(),
                'Middlename' => $user->getMiddlename(),
                'Lastname' => $user->getLastname(),
                'Country' => $user->getCountry(),
                'Email' => $user->getEmail(),
                'Password' => '',
                'Interests' => $interests,
                'hasInterests' => true
            ]), false);

        if ($response->elsSet_UserResult->Result)
        {
            return ['success' => true, 'userId' => $response->elsSet_UserResult->Reply];
        } else
        {
            return [
                'success' => false,
                'code' => 'error_' . $response->elsSet_UserResult->ErrorCode,
                'message' => $response->elsSet_UserResult->Reply
            ];
        }
    }

    /**
     * Delete user profile files
     * @param $userId
     * @param $fileId
     * @return array
     */
    public function deleteUserProfileFile($userId, $fileId): array
    {
        $response = $this->getSoap(
            'elsDelete_UserUploadFile',
            [
                'idsite' => Config::read('currentSiteId'),
                'userid' => $userId,
                'fileId' => $fileId,
            ]
        );

        if ($response->elsDelete_UserUploadFileResult->Result)
        {
            return [
                'success' => $response->elsDelete_UserUploadFileResult->Result
            ];
        } else
        {
            return [
                'success' => false
            ];
        }
    }

}
