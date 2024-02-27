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

use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use JMS\Serializer\SerializerBuilder;
use MainBundle\Entity\Book;
use MainBundle\Services\BookService;
use MainBundle\Services\ReadingListService;
use MBComponents\Controller\Controller;
use MBComponents\HttpFoundation\Request;
use MBComponents\Services\SlxWebService;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Slim\Http\Response;
use Slim\View;
use stdClass;
use Symfony\Component\Form\Form;
use UserBundle\Entity\Address;
use UserBundle\Entity\Faq;
use UserBundle\Entity\Institution;
use UserBundle\Entity\RequestRegister;
use UserBundle\Entity\User;
use UserBundle\Form\AddressType;
use UserBundle\Form\ChangePasswordFormType;
use UserBundle\Form\DetailsType;
use UserBundle\Form\InstitutionType;
use UserBundle\Services\UserService;

/**
 * Class ProfileController
 * @package UserBundle\Controller
 */
class ProfileController extends Controller
{


    /**
     * Profile edit user personal details
     * @throws Exception
     */
    public function personalDetails(): Response
    {
        $this->redirectFirstPage();
        /** @var array $countries */
        $countries = $this->getSlx()->getSlxWebService()
            ->getCountries($this->language, $this->region);
        /** @var UserService $userService */
        $userService = $this->getSlx()->getUserService();
        /** @var SlxWebService $slxWebService */
        $slxWebService = $this->getSlx()->getSlxWebService();
        $userService->getUserFromIdentifier($this->getUser()->getUserId());
        /** @var User $oldData */
        $user = $this->getUser();
        /** Create details type form */
        /** @var Form $detailsForm */
        $detailsForm = $this->createForm(DetailsType::class, $user, [
            'method' => 'PUT',
            'language' => $this->language,
            'countries' => $countries]);
        $detailsForm->handleRequest($this->getRequest());
        /** If request is valid update user info  */
        if ($detailsForm->isSubmitted() && $detailsForm->isValid()) {
            /** Update user basic info */
            $res = $userService->updateUser($user);
            /** If result is success update user in session */
            if ($res['success']) {
                //get user from webservive and fill session
                $userService->getUserFromIdentifier($user->getUserId());
                /** Return success true */
                return $this->renderJson(['success' => true]);
            }

            return $this->renderJson(['success' => false, 'message' => 'invalid request']);
        }

        /** Return my personal details page */
        return $this->render('@UserBundle/profile/my-personal-details.html.twig', [
            'user' => $user,
            'countries' => $countries,
            'detailForm' => $detailsForm->createView(),
            'slxWebService' => $slxWebService
        ]);
    }


    /**
     *  Show user books
     * @throws Exception
     */
    public function showBooks(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $em = $this->getEntityManager();
        /** @var RequestRegister $existentUser */
        $existentUser = $em->getRepository(RequestRegister::class)->findOneBy(['email' => $user->getEmail()]);
        /** @var SlxWebService $slxWebService */
        $slxWebService = $this->getSlx()->getSlxWebService();
        /** @var BookService $bookService */
        $bookService = $this->getSlx()->getBookService();
        /** Get user books */
        $myBooks = $bookService->getUserBooks($user->getUserId());
        /** Get notifications before delete */
        $notifications = $slxWebService->getAllNotifications($user->getUserId(), $this->language, $this->region);
        /** Delete all notifications */
        $slxWebService->updateUserNotification($user->getUserId());
        /** Get courses list */
        $coursesList = $bookService->getCoursesList($this->region, $this->language);
        $booksHistory = $bookService->getBooksHistory($user->getUserId(), true);
        $nbBooksHistory = count($booksHistory) > 0 ? $booksHistory['0'] : 0;
        /** Return my books page view */
        return $this->render('@UserBundle/profile/my-books.html.twig', [
            'user' => $this->getUser(),
            'userPhase' => $existentUser ? $existentUser->getMyIcGuide() : null,
            'myBooks' => $myBooks,
            'coursesList' => $coursesList,
            'notifications' => $notifications,
            'nbBooksHistory' => $nbBooksHistory
        ]);
    }


    /**
     * @return Response
     * @throws Exception
     */
    public function uploadUserFile(): Response
    {
        /** @var UserService $userService */
        $userService = $this->getSlx()->getUserService();
        /** Get the request sent */
        $request = $this->getRequest();
        /** Get the user id from the actual user if he's logged or from request if he is registering  */
        $this->isLoggedIn() ? $userId = $this->getUser()->getUserId() : $userId = $request->get('userId');
        /** Get the file name */
        $fileName = $_FILES['userFile']['name'];
        /** Upload file in SLX */
        $result = $userService->uploadFile($userId, $fileName, $_FILES['userFile']);
        if ($result['success']) {
            $this->getSession()->set('fileUploaded', true);
            //update user session
            return $this->renderJson(['success' => true]);
        }
        return $this->renderJson(['success' => false, 'message' => $result['message']]);
    }

    /**
     *  Show user education consultant page
     * @throws Exception
     */
    public function showEducationConsultant(): Response
    {
        $this->redirectFirstPage();
        /** Return my education consultant page view */
        return $this->render('@UserBundle/profile/my-education-consultant.html.twig', [
            'user' => $this->getUser()]);
    }

    /** Institutions Block functions */

    /**
     * Profile user institutions
     * @throws Exception
     */
    public function showInstitutions(): Response
    {
        $this->redirectFirstPage();
        /** @var User $oldData */
        $user = $this->getUser();
        /** @var SlxWebService $slxService */
        $slxService = $this->getSlx()->getSlxWebService();
        /** Get institutions list from user country */
        $institutions = [];
        if ($user->getCountry()) {
            $institutions = $slxService->getInstitutions($this->fixCountryName($user->getCountry()));
        }
        /** Create professions array */
        $professions = [];
        /** @var array $category */
        foreach ($this->getCategories() as $category) {
            if (isset($category['category'])) {
                $professions[$category['category']] = $category['category'];
            }
        }
        /** Create add new institution form */
        $newInstitutionForm = $this->createForm(
            InstitutionType::class,
            new Institution(),
            ['institutions' => $institutions, 'professions' => $professions]
        );
        /** Load countries and user country's institutions */
        $countries = $slxService->getCountries($this->language, $this->region);
        /** Return my institutions page view */
        return $this->render('@UserBundle/profile/institutions/my-institutions.html.twig', [
            'user' => $user,
            'institutions' => $institutions,
            'professions' => $professions,
            'slxWebService' => $slxService,
            'countries' => $countries,
            'newInstitutionForm' => $newInstitutionForm->createView()
        ]);
    }

    /**
     * faq page
     * @throws Exception
     */
    public function faq(): Response
    {
        /** Get site region */
        $reg = $this->region;
        /** Get site language */
        $lang = $this->language;
        $faqLang = $lang == "en" && $reg == "1" ? "anz" : $lang;
        /** Get faq from DB with ascending oder */
        /** @var Faq $faq */
        $faq = $this->getEntityManager()->getRepository(Faq::class)->findBy(
            ['language' => $faqLang],
            ['order' => 'ASC']
        );
        /** Render faq page */
        return $this->render('@UserBundle/profile/faq.html.twig', ['faqs' => $faq]);
    }

    /**
     * updateProfileFileProvided
     * @throws Exception
     */
    public function updateProfileFileProvided(): void
    {
        $user = $this->getUser();
        $userId = $this->getUser()->getUserId();
        $fileId = $this->getRequest()->request->get('fileId');
        /** @var UserService $userService */
        $userService = $this->getSlx()->getUserService();
        /** Update user basic info */
        $res = $userService->deleteUserProfileFile($userId , $fileId);
        /** If result is success update user in session */
        if ($res['success']) {
            $user->setProfileFile([]);
            $user->setProfileFileProvided( 0 );
            $this->getSession()->remove('fileUploaded');
            /** Update user in the session */
            $userService->saveUserIntoSession($user);
        }
    }
    
    /**
     * @return Response
     * @throws Exception
     */
    public function changePassword(): Response
    {
        $this->redirectFirstPage();
        $request = $this->getRequest();
        /** @var User $user */
        $user = $this->getUser();
        /** Render faq page */
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userService = $this->getSlx()->getUserService();
            $userID = $user->getUserId();
            $login = $userService->userLogin($user->getEmail(), $form->getData()['oldPassword']);
            if (!$login['success']) {
                /** Return success true */
                return $this->renderJson(['success' => false, 'message' => $this->trans('user.change_password.error')]);
            } else {
                /** update the user data with the new password*/
                $response = $userService->updatePassword($userID, $form->getData()['password']);
                if ($response->Result) {
                    $this->getSession()->set('password_updated', true);
                    /** send email to notify users */
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
                } else {
                    $this->getSession()->set('password_updated', false);
                }
                return $this->renderJson(['success' => true]);
            }

        }
        return $this->render('@UserBundle/profile/change-password.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }

    /**
     * Set address info from institution address
     * @param Institution $newInstitution
     * @return Address
     * @throws Exception
     */
    public function setAddressInfo(Institution $newInstitution): Address
    {
        /** @var array $countries */
        $countries = null;
        /** @var SlxWebService $slxService */
        $slxService = $this->getSlx()->getSlxWebService();
        /** Load countries and user country's institutions */
        $countries = $slxService->getCountries($this->language, $this->region);
        /** @var Address $address */
        $newAddress = new Address();
        /** Populate addresses input */
        $addressInfo = $slxService->getAddress($newInstitution->getDepartmentId());
        /** If address info exists set address information */
        if ($addressInfo) {
            $newAddress->setCity($addressInfo->City);
            $newAddress->setState($addressInfo->State);
            $newAddress->setAddress1($addressInfo->Address1);
            $newAddress->setAddress2($addressInfo->Address2);
            $newAddress->setAddress3($addressInfo->Address3);
            $newAddress->setAddress4($addressInfo->Address4);
            $newAddress->setPostalCode($addressInfo->Postalcode);
            /** If address info has country set it to the new address */
            if ($addressInfo->Country) {
                $newCountry = new stdClass();
                $newCountry->Text = $addressInfo->Country;
                array_push($countries, $newCountry);
                /** Set country to newAddress */
                $newAddress->setCountry($addressInfo->Country);
            }
        }
        /** Return the new address */
        return $newAddress;
    }

    /**
     * Add user institution via ajax request
     * @throws Exception
     */
    public function addUserInstitution(): ?Response
    {
        /** @var User $user */
        $user = $this->getUser();
        /** @var UserService $userService */
        $userService = $this->getSlx()->getUserService();
        /** Get new institution from request */
        $newInstitution = $this->getInstitutionFromRequest($this->getRequest());
        /** Add new institution to the user */
        $result = $userService->addUserInstitution($user->getUserId(), $newInstitution);

        /** @var Address $address */
        $newAddress = new Address();
        /** If the new institution has been successfully saved into slx */
        if ($result['success']) {
            /** Set new institution ID */
            $newInstitution->setId($result['reply']);

            /** Add the new institution to the user institutions*/
            $user->addInstitution($newInstitution);
            /** @var array $newAddressInfo */
            $newAddressInfo = null;
            /**
             * Check if the new institution is primary then update the old primary to be normal
             * @var Institution $institution
             */
            if ($newInstitution->isPrimary()) {
                /** @var Institution $institution
                 */
                foreach ($user->getInstitutions() as $institution) {
                    /** For each institution test if it's the new primary one */
                    if ($institution->getId() !== $newInstitution->getId()) {
                        /** Set the institution as not primary */
                        $institution->setIsPrimary(false);
                        /** Update user institution */
                        $user->updateInstitution($institution);
                        break;
                    }
                }
                if ($newInstitution->getDepartmentId()) {
                    /** Populate addresses input */
                    $newAddress = $this->setAddressInfo($newInstitution);
                }
                /** Set Institution Name to address2 */
                $newAddress->setAddress2($newInstitution->getInstitutionName());
                /** Set Department Name to address3 */
                $newAddress->setAddress3($newInstitution->getDepartmentName());
                /** Serialize data from object to array */
                $serializer = SerializerBuilder::create()->build();
                $newAddressInfo = $serializer->serialize($newAddress, 'json');
            }
            /** Re save user into session after institutions modification */
            $userService->saveUserIntoSession($user);

            /** Insert the new country */
            $userCountry = $this->getRequest()->request->get('country');
            /** @var UserService $userService */
            $userService = $this->getSlx()->getUserService();
            /** add user country */
            if ($userCountry != '') {
                $user->setCountry($userCountry);
                /** Update user basic info */
                $res = $userService->updateUser($user);
                /** If result is success update user in session */
                if ($res['success']) {
                    /** Update user in the session */
                    $userService->saveUserIntoSession($user);
                }
            }
            /**
             * Render institution details view
             * @var View $view
             */
            $view = $this->renderView('@UserBundle/profile/institutions/institution-details.html.twig', [
                'institution' => $newInstitution
            ]);
            return $this->renderJson([
                'success' => true,
                'institution' => [
                    'id' => $newInstitution->getId(),
                    'institution_id' => $newInstitution->getInstitutionId(),
                    'institution_name' => $newInstitution->getInstitutionName(),
                    'department_id' => $newInstitution->getDepartmentId(),
                    'department_name' => $newInstitution->getDepartmentName(),
                    'profession' => $newInstitution->getProfession(),
                    'speciality' => $newInstitution->getSpeciality(),
                    'primary' => $newInstitution->isPrimary()
                ],
                'address' => $newAddressInfo ? json_decode($newAddressInfo) : '',
                'view' => $view]);
        }

        return $this->renderJson(['success' => false, 'message' => $result['reply']]);
    }

    /**
     * Update user institution
     * @throws Exception
     */
    public function updateUserInstitution(): ?Response
    {
        /** @var User $user */
        $user = $this->getUser();
        /** Get the institutionToUpdate from the form request */
        $institutionToUpdate = $this->getInstitutionFromRequest($this->getRequest());
        /** @var UserService $userService */
        $userService = $this->getSlx()->getUserService();
        /** Res get user institution update */
        $res = $userService->updateUserInstitution($user->getUserId(), $institutionToUpdate);
        /** If result is success */
        if ($res['success']) {
            /** Update user institutions saved in session */
            $user->updateInstitution($institutionToUpdate);
            /**
             * Check if the updated institution is set to primary then update the old primary to be normal
             * @var Institution $institution
             */
            if ($institutionToUpdate->isPrimary()) {
                foreach ($user->getInstitutions() as $institution) {
                    if ($institution->getId() !== $institutionToUpdate->getId() && $institution->isPrimary()) {
                        $institution->setIsPrimary(false);
                        $userService->updateUserInstitution($user->getUserId(), $institution);
                        $user->updateInstitution($institution);
                        $res['primary'] = true;
                    }
                }
            }
            /** Re save user into session after institutions modification */
            $userService->saveUserIntoSession($user);
            $res['institution'] = [
                'id' => $institutionToUpdate->getId(),
                'institution_id' => $institutionToUpdate->getInstitutionId(),
                'institution_name' => $institutionToUpdate->getInstitutionName(),
                'department_id' => $institutionToUpdate->getDepartmentId(),
                'department_name' => $institutionToUpdate->getDepartmentName(),
                'profession' => $institutionToUpdate->getProfession(),
                'speciality' => $institutionToUpdate->getSpeciality(),
                'primary' => $institutionToUpdate->isPrimary()
            ];
            /**
             * Render institution details view
             * @var View $view
             */
            $view = $this->renderView('@UserBundle/profile/institutions/institution-details.html.twig', [
                'institution' => $institutionToUpdate
            ]);
            /** Return the result, new institution and new institution details view */
            return $this->renderJson([
                'success' => true,
                'institution' => [
                    'id' => $institutionToUpdate->getId(),
                    'institution_id' => $institutionToUpdate->getInstitutionId(),
                    'institution_name' => $institutionToUpdate->getInstitutionName(),
                    'department_id' => $institutionToUpdate->getDepartmentId(),
                    'department_name' => $institutionToUpdate->getDepartmentName(),
                    'profession' => $institutionToUpdate->getProfession(),
                    'speciality' => $institutionToUpdate->getSpeciality(),
                    'primary' => $institutionToUpdate->isPrimary()
                ]
                , 'view' => $view]);
        }

        return $this->renderJson(['success' => false, 'message' => 'invalid form']);
    }

    /**
     * Delete user institution
     * @throws Exception
     */
    public function deleteUserInstitution(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        /** @var UserService $userService */
        $userService = $this->getSlx()->getUserService();
        /** Get institution Id from request */
        $institutionId = $this->getRequest()->get('institution');
        /** @var  $institutionToDelete */
        $institutionToDelete = null;
        /** @var  $otherInstitution */
        $otherInstitution = null;
        /**
         * Before deleting the institution check if it exist & there are other institutions not disabled left
         * @var Institution $institution
         */
        foreach ($user->getInstitutions() as $institution) {
            if ($institution->getId() == $institutionId) {
                $institutionToDelete = $institution;
            } elseif ($institution->isEnabled()) {
                $otherInstitution = $institution;
            } else {
                // SonarQube Rule
            }
        }
        if ($otherInstitution && $institutionToDelete && !$institutionToDelete->isHasRequest()) {
            $res = $userService->deleteUserInstitution($user->getUserId(), $institutionToDelete->getId());
            /** If result is successful set next institution to primary */
            if ($res['success']) {
                /** If the deleted institution was primary then set the next one to be primary */
                if ($institutionToDelete->isPrimary()) {
                    $otherInstitution->setIsPrimary(true);
                    $userService->updateUserInstitution($user->getUserId(), $otherInstitution);
                    $user->updateInstitution($otherInstitution);
                    $res['primary'] = $otherInstitution->getId();
                }
                /** Remove element of institution to delete */
                $user->getInstitutions()->removeElement($institutionToDelete);
                /** Save user info session */
                $userService->saveUserIntoSession($user);
                /** Return result */
                return $this->renderJson($res);
            }
        } elseif (!$otherInstitution) {
            return $this->renderJson([
                'success' => false,
                'reply' => $this->trans('user.profile.institutions.require_one')
            ]);
        } else {
            // SonarQube Rule
        }

        return $this->renderJson(['success' => false, 'reply' => 'invalid request']);
    }


    /**
     * Disable user institution
     * @throws Exception
     */
    public function disableUserInstitution(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        /** @var UserService $userService */
        $userService = $this->getSlx()->getUserService();
        /**
         * Get institution id
         * @var  $institutionId
         */
        $institutionId = $this->getRequest()->get('institution');
        /** @var  $institutionToDisable */
        $institutionToDisable = null;
        /** @var  $otherInstitution */
        $otherInstitution = null;
        /**
         * Before disabling the institution check if it exist & there are other institutions not disabled left
         * @var Institution $institution
         */
        foreach ($user->getInstitutions() as $institution) {
            if ($institution->getId() == $institutionId) {
                $institutionToDisable = $institution;
            } elseif ($institution->isEnabled()) {
                $otherInstitution = $institution;
            } else {
                //SonarQube Rule
            }
        }
        if ($otherInstitution && $institutionToDisable && $institutionToDisable->isHasRequest()) {
            /** Set enable to false the institution to enable */
            $institutionToDisable->setEnabled(false);
            $res = $userService->updateUserInstitution($user->getUserId(), $institutionToDisable);
            if ($res['success']) {
                /** If the disabled institution was primary then set the next one to be primary */
                if ($institutionToDisable->isPrimary()) {
                    $otherInstitution->setIsPrimary(true);
                    $institutionToDisable->setIsPrimary(false);
                    $userService->updateUserInstitution($user->getUserId(), $otherInstitution);
                    $user->updateInstitution($otherInstitution);
                    $res['primary'] = $otherInstitution->getId();
                }
                /** If the institution is not primary update the user institution */
                $user->updateInstitution($institutionToDisable);
                /** Save user info into the session */
                $userService->saveUserIntoSession($user);
                /** Return result */
                return $this->renderJson($res);
            }
        } elseif (!$otherInstitution) {
            return $this->renderJson([
                'success' => false,
                'reply' => $this->trans('user.profile.institutions.require_one')
            ]);
        } else {
            //SonarQube Rule
        }

        return $this->renderJson(['success' => false, 'reply' => 'invalid request']);
    }

    /**
     * Retrieve institution information from post request
     * @param Request $request
     * @return Institution
     */
    private function getInstitutionFromRequest(Request $request): Institution
    {
        $institution = new Institution();
        /** Set the institution id from the request */
        if ($request->request->has('institution_id')) {
            $institution->setId($request->get('institution_id'));
        }
        /** Set the primary institution from the request */
        if ($request->request->has('institution_primary')) {
            $institution->setIsPrimary($request->get('institution_primary'));
        }
        $institution->setInstitutionId($request->get('institution_institution_id'));
        /** Set institution name from the request */
        $institution->setInstitutionName($request->get('institution_institution_name'));
        /** Set department id from the request */
        $institution->setDepartmentId($request->get('institution_department_id'));
        /** Set department name from the request */
        $institution->setDepartmentName($request->get('institution_department_name'));
        /** Set profession from the request */
        $institution->setProfession($request->get('institution_profession'));
        /** Set specialty from the request */
        $institution->setSpeciality($request->get('institution_speciality'));
        return $institution;
    }


    /**
     * Update user address
     * @throws Exception
     */
    public function updateUserAddress(): ?Response
    {
        /** @var User $user */
        $user = $this->getUser();
        /**
         * Get address to update from request
         * @var  $addressToUpdate
         */
        $addressToUpdate = $this->getAddressFromRequest($this->getRequest());
        /** @var UserService $userService */
        $userService = $this->getSlx()->getUserService();
        $res = $userService->addOrUpdateUserAddress($user->getUserId(), $addressToUpdate);
        if ($res['success']) {
            /** Update user institutions  */
            $user->updateAddress($addressToUpdate);
            /** Save new updates in session */
            $userService->saveUserIntoSession($user);
            $view = $this->renderView('@UserBundle/profile/address/address-details.html.twig', [
                'address' => $addressToUpdate
            ]);
            return $this->renderJson([
                'success' => true,
                'address' => [
                    'id' => $addressToUpdate->getId(),
                    'address1' => $addressToUpdate->getAddress1(),
                    'address2' => $addressToUpdate->getAddress2(),
                    'address3' => $addressToUpdate->getAddress3(),
                    'address4' => $addressToUpdate->getAddress4(),
                    'state' => $addressToUpdate->getState(),
                    'postalCode' => $addressToUpdate->getPostalCode(),
                    'city' => $addressToUpdate->getCity(),
                    'country' => $addressToUpdate->getCountry()
                ]
                , 'view' => $view]);
        }

        return $this->renderJson(['success' => false, 'message' => 'invalid request']);
    }

    /**
     * Add user address from post request
     * @throws Exception
     */
    public function addUserAddress(): ?Response
    {
        $user = $this->getUser();
        /** Get address from post request */
        $newAddress = $this->getAddressFromRequest($this->getRequest());
        $userService = $this->getSlx()->getUserService();
        /** Send address data to slx */
        if ($newAddress->getCountry() != '' && $user->getCountry() == '') {
            $user->setCountry($newAddress->getCountry());
            /** Update user basic info */
            $userService->updateUser($user);
        }

        $res = $userService->addOrUpdateUserAddress($user->getUserId(), $newAddress);
        if ($res['success']) {
            /** Add user new address to the session */
            $newAddress->setId($res['reply']);
            $user->addAddress($newAddress);
            $userService->saveUserIntoSession($user);
            $res['reply'] = $this->renderView('@UserBundle/profile/address/address-block.html.twig', [
                'order' => $user->getAddresses()->count(),
                'totalAddresses' => $user->getAddresses()->count(),
                'address' => $newAddress
            ]);
            /** Re save user into session after institutions modification */
            $userService->saveUserIntoSession($user);
            /**
             * Render address details view
             * @var View $view
             */
            $view = $this->renderView('@UserBundle/profile/address/address-details.html.twig', [
                'address' => $newAddress
            ]);
            return $this->renderJson([
                'success' => true,
                'address' => [
                    'id' => $newAddress->getId(),
                    'canBeDeleted' => $newAddress->getCanBeDeleted(),
                    'address1' => $newAddress->getAddress1(),
                    'address2' => $newAddress->getAddress2(),
                    'address3' => $newAddress->getAddress3(),
                    'address4' => $newAddress->getAddress4(),
                    'state' => $newAddress->getState(),
                    'postalCode' => $newAddress->getPostalCode(),
                    'city' => $newAddress->getCity(),
                    'country' => $newAddress->getCountry(),
                    'phone' => $newAddress->getPhone()
                ]
                , 'view' => $view]);
        }

        return $this->renderJson(['success' => false, 'message' => 'invalid request']);
    }


    /**
     * Disable address when have request
     * @return Response
     * @throws Exception
     */
    public function disableUserAddress(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        /** @var UserService $userService */
        $userService = $this->getSlx()->getUserService();
        /** Get address id from request */
        $addressId = $this->getRequest()->get('address');
        /** @var  $addressToDisable */
        $addressToDisable = null;
        /**
         * Before disabling the address check if it exist & there are other addresses not disabled left
         * @var Address $address
         */
        foreach ($user->getAddresses() as $address) {
            if ($address->getId() == $addressId) {
                $addressToDisable = $address;
            }
        }

        /** Disable the address  */
        if ($addressToDisable && $addressToDisable->getType() == null) {
            /** Set type disabled to address */
            $addressToDisable->setType('Disabled');
            /** Get disable user address result */
            $res = $userService->disableUserAddress($user->getUserId(), $addressId, 'true');
            /** If result is successful update address to disable */
            if ($res['success']) {
                $user->updateAddress($addressToDisable);
                /** Save changes into session */
                $userService->saveUserIntoSession($user);
                return $this->renderJson($res);
            }
        }
        return $this->renderJson(['success' => false, 'reply' => 'invalid request']);
    }

    /**
     * Delete user address
     * @throws Exception
     */
    public function deleteUserAddress(): ?Response
    {
        /** @var User $user */
        $user = $this->getUser();
        /** @var UserService $userService */
        $userService = $this->getSlx()->getUserService();
        /** Get address id from request */
        $addressId = $this->getRequest()->get('address');
        /** @var  $addressToDelete */
        $addressToDelete = null;
        /**
         * Before deleting the address check if it exist
         * @var Address $address
         */
        foreach ($user->getAddresses() as $address) {
            if ($address->getId() == $addressId) {
                $addressToDelete = $address;
            }
        }
        /** Get delete user address result */
        $res = $userService->deleteUserAddress($user->getUserId(), $addressToDelete->getId());
        if ($res['success']) {
            /** If result is success remove address deleted */
            $user->getAddresses()->removeElement($addressToDelete);
            /** Save changes into session */
            $userService->saveUserIntoSession($user);
            /** Return result */
            return $this->renderJson($res);
        }

        return $this->renderJson(['success' => false, 'reply' => 'invalid request']);
    }

    /**
     * Retrieve address information from post request
     * @param Request $request
     * @return Address
     */
    private function getAddressFromRequest(Request $request): Address
    {
        /** @var Address $address */
        $address = new Address();
        /** If request has id, set id to the address */
        if ($request->request->has('id')) {
            $address->setId($request->get('id'));
        }
        /** Set all address request info to address */
        $address->setAddress1($request->get('address1'));
        $address->setAddress2($request->get('address2'));
        $address->setAddress3($request->get('address3'));
        $address->setAddress4($request->request->has('address4') ? $request->get('address4') : '');
        $address->setCity($request->get('city'));
        $address->setPostalCode($request->get('postal_code'));
        $address->setState($request->get('state'));
        $address->setCountry($request->get('country'));
        $address->setPhone($request->get('phone'));
        $address->setCanBeDeleted($request->get('can_be_deleted'));
        /** Return the address with request info */
        return $address;
    }

    /**
     * Return address info by address id
     * @param $addressId
     * @return Response
     * @throws Exception
     */
    public function getAddressDetails($addressId): Response
    {
        $user = $this->getUser();
        /** @var Address $address */
        foreach ($user->getAddresses() as $address) {
            if ($address->getId() == $addressId) {
                /** Return all address details */
                return $this->renderJson([
                    'success' => true,
                    'address' => [
                        'id' => $address->getId(),
                        'canBeDeleted' => $address->getCanBeDeleted(),
                        'address1' => $address->getAddress1(),
                        'address2' => $address->getAddress2(),
                        'address3' => $address->getAddress3(),
                        'address4' => $address->getAddress4(),
                        'state' => $address->getState(),
                        'postalCode' => $address->getPostalCode(),
                        'city' => $address->getCity(),
                        'country' => $address->getCountry(),
                        'phone' => $address->getPhone()
                    ]
                ]);
            }
        }
        /** If result is false return message 'address not found' */
        return $this->renderJson(['success' => false, 'message' => 'Address not found']);
    }

    /** set the user version to 0 after click on notification icon
     * @return \Slim\Http\Response
     * @throws \Exception
     */
    public function updateUserVersion()
    {
        $user = $this->getUser();
        if ($user->getIcProfileVersion() !== "0" && $user->getIcProfileVersion() !== null) {
            $user->setIcProfileVersion("0");
            $userService = $this->getSlx()->getUserService();
            $userService->updateUser($user);
            $userService->saveUserIntoSession($user);
        }
        return $this->renderJson(['success' => true]);
    }


    /***  get reading lists page
     * @throws Exception
     */
    public function showReadingLists()
    {
        $this->redirectFirstPage();
        $user = $this->getUser();
        $userId = $user->getUserId();
        /** Create professions array */
        $professions = [];
        /** Get institutions list from user country */
        $institutions = [];
        /** @var BookService $bookService */
        $bookService = $this->getSlx()->getBookService();
        /** @var SlxWebService $slxService */
        $slxService = $this->getSlx()->getSlxWebService();
        /** Get catalogue language */
        $catalogueLang = $this->getApp()->session->get('lang');
        /** Get site language */
        $siteLang = $this->getApp()->session->get('site-lang') ?? $catalogueLang;
        /** @var ReadingListService $readingListService */
        $readingListService = $this->getSlx()->getReadingListService();
        /** Get user reading lists */
        $readingLists = $readingListService->getReadingLists($userId);
        /** Get old requested books */
        $requestedBooks = $bookService->getUserRequestedBooks($userId);

        //push the requested book in the readingList
        if(count($requestedBooks) > 0) {
            $readingList = new stdClass();
            $readingList->ListName = 'Requested Copies';
            $readingList->Status = 'REQUESTED';
            $readingList->ReadingListID='REQUESTEDBOOKS';
            $readingList->ListItems = new stdClass();
            $readingList->ListItems->ReadingListItem = $requestedBooks;
            $readingLists->add($readingList);
        }
        $slxWebService = $this->getSlx()->getSlxWebService();
        /** Delete all notifications */
        $slxWebService->updateUserNotification($user->getUserId());
        /** Get courses list */
        $coursesList = $bookService->getCoursesList($this->region, $this->language);
        /** Get courses level */
        $courseLevels = $bookService->getCourseLevels($this->region, $siteLang);

        if ($user->getCountry()) {
            $institutions = $slxService->getInstitutions($this->fixCountryName($user->getCountry()));
        }
        /** @var array $category */
        foreach ($this->getCategories() as $category) {
            if (isset($category['category'])) {
                $professions[$category['category']] = $category['category'];
            }
        }
        return $this->render('@UserBundle/profile/reading-lists.html.twig', [
            'readingLists' => $readingLists,
            'institutions' => $user->getInstitutions(),
            'courses' => $coursesList,
            'levels' => $courseLevels,
            'user' => $user,
            'professions'=> $professions,
            'institutionsModal' => $institutions
        ]);
    }




    /**
     * get historical data page
     * @return \Slim\Http\Response
     * @throws \Exception
     */
    public function showHistoricalData()
    {
        $user = $this->getUser();
        /** @var BookService $bookService */
        $bookService = $this->getSlx()->getBookService();

        /** Get user history books */
        $booksHistory = $bookService->getBooksHistory($user->getUserId());

        /** Get user approved and pending book */
        $requestedBooks = $bookService->getRequestedBooks($user->getUserId());
        $source = '';
        if (isset($_GET['source'])) {
            $source = $this->cleanMe($_GET['source']);
        }
        /** Return my historical data page view */
        return $this->render('@UserBundle/profile/historical-data.html.twig', [
            'user' => $this->getUser(),
            'myBooks' => $booksHistory,
            'requestedBooks' => $requestedBooks,
            'source' => $source
        ]);
    }

    /**
     * Update user phase when he sees the guide
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws Exception
     */
    public function updateGuide()
    {
        $user = $this->getUser();
        if ($user) {
            /** Get the phase from the ajax request */
            $phase = $this->getRequest()->request->get('phase');
            $em = $this->getEntityManager();
            /** @var RequestRegister $existentUser */
            $existentUser = $em->getRepository(RequestRegister::class)->findOneBy(['email' => $user->getEmail()]);
            /** If the users sees the guide for the phase 2 the user will be deleted from the table  */
            if ($phase === "phase2 done") {
                $em->remove($existentUser);
                $em->flush();
            } /** else the phase will be updated */
            else {
                $existentUser->setMyIcGuide($phase);
                $em->persist($existentUser);
                $em->flush();
            }
            return $this->renderJson([
                'success' => true
            ]);
        } else {
            return $this->renderJson([
                'success' => false
            ]);
        }

    }


    /**
     * remove book from single reading list or from all
     * @return Response
     * @throws Exception
     */
    public function removeBook()
    {
        $request = $this->getRequest();
        $readingListID = $request->get('readingListID');
        $isbn = $request->get('isbn');
        $title = $this->getRequest()->get('bookTitle');
        $details = "<span class='title-history'>".$title. "</span> was removed from the list";
        $option = $request->get('option');
        if ($this->getUser()) {
            $userId = $this->getUser()->getUserId();
            $readingListService = $this->getSlx()->getReadingListService();
            if (intval($option) === 0) {
                //save history
                $readingListService->saveHistory($userId, $readingListID, $details);
                $result = $readingListService->deleteReadingListItem($userId, $readingListID, $isbn);
                return $this->renderJson($result);
            } elseif (intval($option) === 1) {
                try {
                    $readingLists = $readingListService->getReadingListsItem($userId, $isbn);
                    foreach ($readingLists as $readingList) {
                        //save history
                        $readingListService->saveHistory($userId, $readingList, $details);
                        $readingListService->deleteReadingListItem($userId, $readingList, $isbn);
                    }
                    return $this->renderJson(['Result' => true, 'Data' => $readingLists]);
                } catch (\ErrorException $errorException) {
                    return $this->renderJson(['Result' => false, 'Message' => $errorException]);
                }
            } else {
                /** SonarQube rule */
            }
        } else {
            return $this->renderJson(['Result' => false, 'Message' => '']);
        }


    }

    /**
     * @return Response
     * @throws Exception
     */
    public function showCopyModalContent()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $isbn = $request->get('isbn');
        $action = $request->get('action');
        if ($this->getUser()) {
            $userId = $this->getUser()->getUserId();
            /** @var ReadingListService $readingListService */
            $readingListService = $this->getSlx()->getReadingListService();
            $readingLists = $readingListService->getReadingLists($userId, null);
            $readingListsBook = $readingListService->getReadingListsItem($userId, $isbn);

            return $this->render('@UserBundle/profile/reading-list-action-modal-ajax.html.twig', [
                'readingLists' => $readingLists,
                'exists' => $readingListsBook,
                'action' => $action
            ]);
        } else {
            return '';
        }


    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getBookContent()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $readingListId = $request->get('readingListID');
        $isbn = $request->get('isbn');
        $existInAll =   filter_var($request->get('existInAll'),FILTER_VALIDATE_BOOL) ;
        $userId = $this->getUser()->getUserId();
        /** @var ReadingListService $readingListService */
        $readingListService = $this->getSlx()->getReadingListService();
        $book = $readingListService->getReadingListBookContent($userId, $readingListId, $isbn);
        if(!$book) {
            //get book details from webservice getBookDetailsByISBN
            $bookService = $this->getSlx()->getBookService();
            /** Get book details */
            /** @var Book $book */
            $book = $bookService->getBookDetailsByISBN($isbn);
        }
        return $this->render('@UserBundle/profile/reading-list-book-content.html.twig', [
            'book' => $book,
            'readingListID' => $readingListId,
            'existInAll' => $existInAll,
            'user' => $this->getUser()
        ]);

    }

    /***  add reading list
     * @throws Exception
     */
    public function addNewReadingList(): Response
    {
        $readingList = $this->getRequest()->request;
        $user = $this->getUser();
        if ($user) {
            $userId = $user->getUserId();
            /** @var ReadingListService $readingListService */
            $readingListService = $this->getSlx()->getReadingListService();
            /** Get user reading lists */
            $response = $readingListService->setReadingList($userId, $readingList);
            /** If result is success update user in session */
            if ($response['Result']) {
                /** Return success true */
                return $this->renderJson(['success' => true, 'id' => $response['Reply']]);
            } else {
                return $this->renderJson(['success' => false]);
            }
        } else {
            return $this->renderJson(['success' => false]);
        }
    }


    /**
     * @return Response
     * @throws Exception
     */
    public function updateBookCategory()
    {
        $request = $this->getRequest();
        $isbn = $request->get('isbn');
        $readingListID = $request->get('readingListID');
        $category = $request->get('category');
        if ($this->getUser()) {
            $userId = $this->getUser()->getUserId();
            $readingListService = $this->getSlx()->getReadingListService();
            $result = $readingListService->setReadingListItemCategory($userId, $readingListID, $isbn, $category);
            return $this->renderJson(['success' => $result['Result']]);
        } else {
            return $this->renderJson(['success' => false]);
        }

    }

    /**
     * @return Response
     * @throws \PhpOffice\PhpWord\Exception\Exception
     * @throws Exception
     */
    public function exportWord(): Response
    {
        $booksToExport = [];
        /** @var User $user */
        $user = $this->getUser();
        if ($user->getUserId()) {
            $reg = $this->region;
            /** @var BookService $bookService */
            $bookService = $this->getSlx()->getBookService();
            /** Get user books */
            $myBooks = $bookService->getUserBooks($user->getUserId());
            $booksSelected = $this->getRequest()->get('books');

            /** Get data from ajax call */
            foreach ($booksSelected as $value) {
                foreach ($myBooks as $key => $val) {
                    if (strtolower($key) === $value['status']) {
                        foreach ($val as $book) {
                            if ($book['Isbn'] === $value['isbn'] && $book['Details'] !== null) {
                                array_push($booksToExport, $book);
                            }
                        }
                    }
                }
            }
            /** Create a new Word document */
            $phpWord = new PhpWord();
            /** Adding an empty Section to the document */
            $section = $phpWord->addSection();
            $section->getStyle()->setBreakType('continuous');
            $header = $section->addHeader();
            $header->headerTop(10);
            $styleCell = array('borderTopSize' => 1, 'borderTopColor' => 'cccccc', 'borderLeftSize' => 1, 'borderLeftColor' => 'cccccc', 'borderRightSize' => 1, 'borderRightColor' => 'cccccc', 'borderBottomSize' => 1, 'borderBottomColor' => 'cccccc');
            $bookStyle = array('bold' => false, 'italic' => false, 'size' => 12, 'name' => 'Times New Roman', 'afterSpacing' => 0, 'Spacing' => 0, 'cellMargin' => 0);
            $styleBookRow = array('borderBottomSize' => 18, 'borderBottomColor' => 'f0f0f0', 'bgColor' => 'f0f0f0');
            $recLevelStyle = array('bold' => true, 'italic' => false, 'size' => 12, 'color' => 'ffffff', 'name' => 'Times New Roman', 'afterSpacing' => 0, 'Spacing' => 0, 'cellMargin' => 0, 'align' => 'center');
            $styleFirstRow = array('borderBottomSize' => 18, 'borderBottomColor' => 'f0f0f0', 'bgColor' => 'bbbbbb');
            $fontStyle = array('bold' => false, 'italic' => false, 'size' => 12, 'name' => 'Times New Roman', 'afterSpacing' => 0, 'Spacing' => 0, 'cellMargin' => 0);
            $styleTable = array('borderSize' => 6, 'borderColor' => 'cccccc', 'cellMargin' => 20);
            $phpWord->addTableStyle('Book Table', $styleTable);
            $styleInfoTable = array('borderSize' => 6, 'borderColor' => 'cccccc', 'cellMargin' => 20);
            $styleCourseNameRow = array('borderBottomSize' => 18, 'borderBottomColor' => 'cccccc', 'bgColor' => 'f0f0f0');
            $phpWord->addTableStyle('Info Table', $styleInfoTable, $styleCourseNameRow);
            $phpWord->addTableStyle('Course Table', $styleTable);
            $phpWord->addTableStyle('Comment Table', $styleTable);

            /** User info table */
            $infoTable = $section->addTable('Info Table');
            $infoTable->addRow();
            $infoTable->addCell(2000, $styleCell)->addText($this->trans('books_page.export.contant_name'), array('bold' => true, 'italic' => false, 'size' => 12, 'name' => 'Times New Roman'));
            $infoTable->addCell(8000, $styleCell)->addText($user->getFullName(), array('size' => 12, 'color' => '0000ff', 'name' => 'Times New Roman'));
            $infoTable->addRow();
            $infoTable->addCell(2000, $styleCell)->addText($this->trans('books_page.export.email'), array('bold' => true, 'italic' => false, 'size' => 12, 'name' => 'Times New Roman'));
            $infoTable->addCell(8000, $styleCell)->addText($user->getEmail(), array('size' => 12, 'color' => '0000ff', 'name' => 'Times New Roman', 'underline' => 'single'));

            $section->addTextBreak(1);

            $section = $this->exportCommentContent($section, $styleFirstRow, $recLevelStyle);

            foreach ($booksToExport as $myBook) {
                /** @var Book $book */
                $book = $myBook['Details'];
                $year = date('Y', strtotime($book->getPublicationDate()[$this->language]));
                $title = 'Book - ' . htmlspecialchars($book->getTitle());
                $title = ($book->getEditionNumber()) ? $title . ', ' . $book->getEditionNumber() : '';
                $title = ($book->getEditors() != null && $book->getEditors() != '') ? $title . ', ' . $book->getEditors() : '';
                $title = ($book->getEditionNumber()) ? $title . ' - ' . $year : '';
                $allInst = [];
                if (isset($myBook['Institutions']) && count($myBook['Institutions']) > 0) {
                    $institutionList = $myBook['Institutions']['Institution'];
                    if (!array_key_exists(0, $institutionList)) {
                        $allInst[0] = $institutionList;
                    } else {
                        $allInst = $institutionList;
                    }

                    foreach ($allInst as $BookInstitution) {
                        $courseName = isset($BookInstitution['InstitutionCourseName']) ? $BookInstitution['InstitutionCourseName'] : "";
                        $courseCode = isset($BookInstitution['Coursecode']) ? $BookInstitution['Coursecode'] : "";
                        $courseRecLev = isset($BookInstitution['RecLevel']) ? $BookInstitution['RecLevel'] : "";
                        $startDate = isset($BookInstitution['StartDate']) ? $BookInstitution['StartDate'] : "";
                        $students = isset($BookInstitution['Students']) ? $BookInstitution['Students'] : "";

                        if (in_array($reg, ['7', '1', '4', '9'])) {
                            $courseCodeText = '<w:br/>Course code: ' . htmlspecialchars($courseCode);
                        } else {
                            $courseCodeText = " ";
                        }
                        /** Rec Level table */
                        $recLevelTable = $section->addTable('Course Table');
                        $recLevelTable->addRow();
                        $recLevelTable->addCell(null, $styleFirstRow)->addText(htmlspecialchars($courseRecLev) . ' ', $recLevelStyle);
                        /** Book info tables */
                        $table = $section->addTable('Book Table');
                        $table->addRow();
                        $table->addCell(10000, $styleBookRow)->addText(htmlspecialchars($title), $bookStyle);
                        $table->addRow();
                        $table->addCell(10000, $styleCell)->addText('Published date: ' . $book->getPublicationDate()[$this->language] . '<w:br/>Publisher: Elsevier <w:br/>Edition: ' . $book->getEditionNumber() . '<w:br/>ISBN: ' . $book->getIsbn(), $fontStyle);
                        /** Course book table */
                        $courseTable = $section->addTable('Course Table');
                        $courseTable->addRow();
                        $courseTable->addCell(10000, $styleCell)->addText('Course name: ' . htmlspecialchars($courseName) . $courseCodeText . '<w:br/>Start date: ' . $startDate . '<w:br/>Student intake: ' . $students, $fontStyle);
                        $section->addTextBreak(1);
                    }
                } else {
                    /** Book info tables */
                    $table = $section->addTable('Book Table');
                    $table->addRow();
                    $table->addCell(10000, $styleBookRow)->addText(htmlspecialchars($title), $bookStyle);
                    $table->addRow();
                    $table->addCell(10000, $styleCell)->addText('Published date: ' . $book->getPublicationDate()[$this->language] . '<w:br/>Publisher: Elsevier <w:br/>Edition: ' . $book->getEditionNumber() . '<w:br/>ISBN: ' . $book->getIsbn(), $fontStyle);
                    $section->addTextBreak(1);
                }
            }

            $time = date('H:i:s \O\n d/m/Y');
            $date = date('d-m-Y');
            $section->addText($this->trans('books_page.export.generated') . $time . $this->trans('books_page.export.by') . $user->getFullName() . $this->trans('books_page.export.using') . 'inspectioncopy.elsevier.com', array('italic' => true, 'size' => 12, 'name' => 'Times New Roman'));
            ob_clean();
            /** Saving the document as OOXML file */
            $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
            header('Content-Type: application/octet-stream; charset=utf-8');
            header("Content-Disposition: attachment; filename=Book list(" . $date . ").docx");
            $objWriter->save("php://output");
            $wordData = ob_get_contents();
            ob_end_clean();

            return $this->renderJson(['success' => true, 'file' => "data:application/vnd.ms-word;base64," . base64_encode($wordData)]);
        } else {
            return $this->renderJson(['success' => false, 'file' => ""]);
        }

    }

    /**
     * @return Response
     * @throws \PhpOffice\PhpWord\Exception\Exception
     * @throws Exception
     */
    public function exportReadingListWord(): Response
    {
        $booksToExport = [];
        /** @var User $user */
        $user = $this->getUser();
        if ($user) {
            $booksSelected = $this->getRequest()->get('books');
            $listSelected = $this->getRequest()->get('readingListId');

            /** get list with id, get book info to display (if info lucking get them by getBookDetails() )  */
            $readingListService = $this->getSlx()->getReadingListService();
            $readingListInfo = $readingListService->getReadingList($user->getUserId(), $listSelected)[0];

            /** Get data from ajax call */
            foreach ($booksSelected as $value) {
                if (is_array($readingListInfo->ListItems->ReadingListItem)) {
                    foreach ($readingListInfo->ListItems->ReadingListItem as $key => $book) {
                        if ($book->Isbn === $value['isbn']) {
                            array_push($booksToExport, $book);
                        }
                    }
                } else {
                    $book = $readingListInfo->ListItems->ReadingListItem;
                    if ($book->Isbn === $value['isbn']) {
                        array_push($booksToExport, $book);
                    }
                }
            }

            /** Create a new Word document */
            $phpWord = new PhpWord();
            /** Adding an empty Section to the document */
            $section = $phpWord->addSection();
            $section->getStyle()->setBreakType('continuous');
            $header = $section->addHeader();
            $header->headerTop(10);
            $firstCell = array('borderTopSize' => 1, 'borderTopColor' => 'cccccc', 'borderLeftSize' => 1, 'borderLeftColor' => 'cccccc', 'borderRightSize' => 1, 'borderRightColor' => 'cccccc', 'borderBottomSize' => 0, 'borderBottomColor' => 'cccccc');
            $styleCell = array('borderTopSize' => 1, 'borderTopColor' => 'cccccc', 'borderLeftSize' => 1, 'borderLeftColor' => 'cccccc', 'borderRightSize' => 1, 'borderRightColor' => 'cccccc', 'borderBottomSize' => 1, 'borderBottomColor' => 'cccccc');
            $bookStyle = array('bold' => false, 'italic' => false, 'size' => 12, 'name' => 'Times New Roman', 'afterSpacing' => 0, 'Spacing' => 0, 'cellMargin' => 0);
            $styleBookRow = array('borderBottomSize' => 18, 'borderBottomColor' => 'f0f0f0', 'bgColor' => 'f0f0f0');
            $recLevelStyle = array('bold' => true, 'italic' => false, 'size' => 12, 'color' => 'ffffff', 'name' => 'Times New Roman', 'afterSpacing' => 0, 'Spacing' => 0, 'cellMargin' => 0, 'align' => 'center');
            $styleFirstRow = array('borderBottomSize' => 18, 'borderBottomColor' => 'f0f0f0', 'bgColor' => 'bbbbbb');
            $fontStyle = array('bold' => false, 'italic' => false, 'size' => 12, 'name' => 'Times New Roman', 'afterSpacing' => 0, 'Spacing' => 0, 'cellMargin' => 0);
            $styleTable = array('borderSize' => 6, 'borderColor' => 'cccccc', 'cellMargin' => 20);
            $styleInfoTable = array('borderSize' => 6, 'borderColor' => 'cccccc', 'cellMargin' => 20);
            $styleCourseNameRow = array('borderBottomSize' => 18, 'borderBottomColor' => 'cccccc', 'bgColor' => 'f0f0f0');
            $phpWord->addTableStyle('Course name Table', $styleInfoTable, $styleCourseNameRow);
            $phpWord->addTableStyle('Comment Table', $styleTable);
            $phpWord->addTableStyle('Info Table', $styleInfoTable);
            $phpWord->addTableStyle('Course Table', $styleTable);
            $phpWord->addTableStyle('Book Table', $styleTable);

            /** User info and reading list table */
            $infoTable = $section->addTable('Course name Table');
            $infoTable->addRow();
            $textRun = $infoTable->addCell(10000, $firstCell)->addTextRun();
            $textRun->addText('Reading List Name: ', array('bold' => true, 'italic' => false, 'size' => 12, 'name' => 'Times New Roman'));
            $textRun->addText($readingListInfo->ListName, array('size' => 12, 'color' => '0000ff', 'name' => 'Times New Roman', 'underline' => 'single'));

            /** User info and reading list table */
            $infoTable = $section->addTable('Info Table');
            /*$infoTable->addRow();
            $infoTable->addCell(2000, $styleCell)->addText($this->trans('books_page.export.course_code'), array('bold' => true, 'italic' => false, 'size' => 12, 'name' => 'Times New Roman'));
            $infoTable->addCell(8000, $styleCell)->addText($readingListInfo->ModuleCode, array('size' => 12, 'color' => '0000ff', 'name' => 'Times New Roman'));
            $infoTable->addRow();
            $infoTable->addCell(2000, $styleCell)->addText('Course name: ', array('bold' => true, 'italic' => false, 'size' => 12, 'name' => 'Times New Roman'));
            $infoTable->addCell(8000, $styleCell)->addText($readingListInfo->ModuleName, array('size' => 12, 'color' => '0000ff', 'name' => 'Times New Roman'));
            $infoTable->addRow();
            $infoTable->addCell(2000, $styleCell)->addText($this->trans('books_page.export.start_date'), array('bold' => true, 'italic' => false, 'size' => 12, 'name' => 'Times New Roman'));
            $infoTable->addCell(8000, $styleCell)->addText('', array('size' => 12, 'color' => '0000ff', 'name' => 'Times New Roman'));
            $infoTable->addRow();
            $infoTable->addCell(2000, $styleCell)->addText($this->trans('books_page.export.student_intake'), array('bold' => true, 'italic' => false, 'size' => 12, 'name' => 'Times New Roman'));
            $infoTable->addCell(8000, $styleCell)->addText($readingListInfo->ModuleCode, array('size' => 12, 'color' => '0000ff', 'name' => 'Times New Roman'));
            $infoTable->addRow();
            $infoTable->addCell(2000, $styleCell);
            $infoTable->addCell(8000, $styleCell);*/
            $infoTable->addRow();
            $infoTable->addCell(2000, $styleCell)->addText($this->trans('books_page.export.contant_name'), array('bold' => true, 'italic' => false, 'size' => 12, 'name' => 'Times New Roman'));
            $infoTable->addCell(8000, $styleCell)->addText($user->getFullName(), array('size' => 12, 'color' => '0000ff', 'name' => 'Times New Roman'));
            $infoTable->addRow();
            $infoTable->addCell(2000, $styleCell)->addText($this->trans('books_page.export.email'), array('bold' => true, 'italic' => false, 'size' => 12, 'name' => 'Times New Roman'));
            $infoTable->addCell(8000, $styleCell)->addText($user->getEmail(), array('size' => 12, 'color' => '0000ff', 'name' => 'Times New Roman', 'underline' => 'single'));

            $section->addTextBreak(1);

            $section = $this->exportCommentContent($section, $styleFirstRow, $recLevelStyle);

            foreach ($booksToExport as $book) {
                $year = $book->Pubyear;
                $title = 'Book - ' . htmlspecialchars($book->Title);
                $title = ($book->Edition) ? $title . ', ' . $book->Edition : '';
                $title = ($book->Author != null && $book->Author != '') ? $title . ', ' . $book->Author : '';
                $title = ($book->Edition) ? $title . ' - ' . $year : '';

                if ($readingListInfo->Status != 'REQUESTED') {
                    $book->Section == "" ? ($bookSection = $this->trans('reading_list.section_name')) : $bookSection = $book->Section;
                    /** Rec Level table */
                    $bookSectionTable = $section->addTable('Course Table');
                    $bookSectionTable->addRow();
                    $bookSectionTable->addCell(null, $styleFirstRow)->addText(htmlspecialchars($bookSection) . ' ', $recLevelStyle);
                }

                /** Book info tables */
                $table = $section->addTable('Book Table');
                $table->addRow();
                $table->addCell(10000, $styleBookRow)->addText(htmlspecialchars($title), $bookStyle);
                $table->addRow();
                $table->addCell(10000, $styleCell)->addText('Published year: ' . $book->Pubyear . '<w:br/>' . $this->trans('books_page.export.publisher') . ' ' . 'Elsevier <w:br/>' . $this->trans('books_page.export.edition') . ' ' . $book->Edition . '<w:br/>' . $this->trans('books_page.export.isbn') . ' ' . $book->Isbn, $fontStyle);
                $section->addTextBreak(1);
            }

            $time = date('H:i:s \O\n d/m/Y');
            $date = date('d-m-Y');
            $section->addText($this->trans('books_page.export.generated') . $time . $this->trans('books_page.export.by') . $user->getFullName() . $this->trans('books_page.export.using') . 'inspectioncopy.elsevier.com', array('italic' => true, 'size' => 12, 'name' => 'Times New Roman'));
            ob_clean();
            /** Saving the document as OOXML file */
            $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
            header('Content-Type: application/octet-stream; charset=utf-8');
            header("Content-Disposition: attachment; filename=".$this->trans('reading_list.default_list_name')."(" . $date . ").docx");
            $objWriter->save("php://output");
            $wordData = ob_get_contents();
            ob_end_clean();
            return $this->renderJson(['success' => true, 'file' => base64_encode($wordData)]);
        } else {
            return $this->renderJson(['success' => false, 'file' => ""]);
        }

    }

    /**
     * @param $isbn
     * @return Response
     * @throws \MBComponents\Exceptions\NotFoundException
     * @throws \PhpOffice\PhpWord\Exception\Exception
     * @throws Exception
     */
    public function exportBookWord($isbn): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($user) {
            /** @var BookService $bookService */
            $bookService = $this->getSlx()->getBookService();
            /** Get book details */
            /** @var Book $book */
            $book = $bookService->getBookDetailsByISBN($isbn);
            /** Create a new Word document */
            $phpWord = new PhpWord();
            /** Adding an empty Section to the document */
            $section = $phpWord->addSection();
            $section->getStyle()->setBreakType('continuous');
            $header = $section->addHeader();
            $header->headerTop(10);
            $styleCell = array('borderTopSize' => 1, 'borderTopColor' => 'cccccc', 'borderLeftSize' => 1, 'borderLeftColor' => 'cccccc', 'borderRightSize' => 1, 'borderRightColor' => 'cccccc', 'borderBottomSize' => 1, 'borderBottomColor' => 'cccccc');
            $bookStyle = array('bold' => false, 'italic' => false, 'size' => 12, 'name' => 'Times New Roman', 'afterSpacing' => 0, 'Spacing' => 0, 'cellMargin' => 0);
            $styleBookRow = array('borderBottomSize' => 18, 'borderBottomColor' => 'f0f0f0', 'bgColor' => 'f0f0f0');
            $recLevelStyle = array('bold' => true, 'italic' => false, 'size' => 12, 'color' => 'ffffff', 'name' => 'Times New Roman', 'afterSpacing' => 0, 'Spacing' => 0, 'cellMargin' => 0, 'align' => 'center');
            $styleFirstRow = array('borderBottomSize' => 18, 'borderBottomColor' => 'f0f0f0', 'bgColor' => 'bbbbbb');
            $fontStyle = array('bold' => false, 'italic' => false, 'size' => 12, 'name' => 'Times New Roman', 'afterSpacing' => 0, 'Spacing' => 0, 'cellMargin' => 0);
            $styleTable = array('borderSize' => 6, 'borderColor' => 'cccccc', 'cellMargin' => 20);
            $styleInfoTable = array('borderSize' => 6, 'borderColor' => 'cccccc', 'cellMargin' => 20);
            $phpWord->addTableStyle('Comment Table', $styleTable);
            $phpWord->addTableStyle('Info Table', $styleInfoTable);
            $phpWord->addTableStyle('Book Table', $styleTable);

            /** User info table */
            $infoTable = $section->addTable('Info Table');
            $infoTable->addRow();
            $infoTable->addCell(2000, $styleCell)->addText($this->trans('books_page.export.contant_name'), array('bold' => true, 'italic' => false, 'size' => 12, 'name' => 'Times New Roman'));
            $infoTable->addCell(8000, $styleCell)->addText($user->getFullName(), array('size' => 12, 'color' => '0000ff', 'name' => 'Times New Roman'));
            $infoTable->addRow();
            $infoTable->addCell(2000, $styleCell)->addText($this->trans('books_page.export.email'), array('bold' => true, 'italic' => false, 'size' => 12, 'name' => 'Times New Roman'));
            $infoTable->addCell(8000, $styleCell)->addText($user->getEmail(), array('size' => 12, 'color' => '0000ff', 'name' => 'Times New Roman', 'underline' => 'single'));

            $section->addTextBreak(1);

            $section = $this->exportCommentContent($section, $styleFirstRow, $recLevelStyle);

            $year = date('Y', strtotime($book->getPublicationDate()[$this->language]));
            $title = 'Book - ' . htmlspecialchars($book->getTitle());
            $title = ($book->getEditionNumber()) ? $title . ', ' . $book->getEditionNumber() : '';
            $title = ($book->getEditors() != null && $book->getEditors() != '') ? $title . ', ' . $book->getEditors() : '';
            $title = ($book->getEditionNumber()) ? $title . ' - ' . $year : '';

            /** Book info tables */
            $table = $section->addTable('Book Table');
            $table->addRow();
            $table->addCell(10000, $styleBookRow)->addText(htmlspecialchars($title), $bookStyle);
            $table->addRow();
            $table->addCell(10000, $styleCell)->addText('Published year: ' . $year. '<w:br/>' . $this->trans('books_page.export.publisher') . ' ' . 'Elsevier <w:br/>' . $this->trans('books_page.export.edition') . ' ' . $book->getEditionNumber(). '<w:br/>' . $this->trans('books_page.export.isbn') . ' ' . $book->getIsbn(), $fontStyle);
            $section->addTextBreak(1);

            $time = date('H:i:s \O\n d/m/Y');
            $date = date('d-m-Y');
            $section->addText($this->trans('books_page.export.generated') . $time . $this->trans('books_page.export.by') . $user->getFullName() . $this->trans('books_page.export.using') . 'inspectioncopy.elsevier.com', array('italic' => true, 'size' => 12, 'name' => 'Times New Roman'));
            ob_clean();
            /** Saving the document as OOXML file */
            $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
            header('Content-Type: application/octet-stream; charset=utf-8');
            header("Content-Disposition: attachment; filename=Reading list(" . $date . ").docx");
            $objWriter->save("php://output");
            $wordData = ob_get_contents();
            ob_end_clean();
            return $this->renderJson(['success' => true, 'file' => base64_encode($wordData)]);
        } else {
            return $this->renderJson(['success' => false, 'file' => ""]);
        }

    }

    /**
     * @param $section
     * @param $styleFirstRow
     * @param $recLevelStyle
     * @return mixed
     */
    public function exportCommentContent($section, $styleFirstRow, $recLevelStyle)
    {

        /** Comments table */

        $commentsTable = $section->addTable('Course Table');
        $commentsTable->addRow();
        $commentsTable->addCell(null, $styleFirstRow)->addText($this->trans('books_page.export.comments'), $recLevelStyle);

        /** Content table*/
        $contentTable = $section->addTable('Comment Table');
        $contentTable->addRow();
        $cellRowSpan = array('vMerge' => 'restart', 'borderColor' => 'cccccc');
        $contentTable->addCell(10000, $cellRowSpan)->addText($this->trans('books_page.export.comments_text') . '<w:br/><w:br/>', array('italic' => true, 'color' => '8eaadb', 'size' => 12, 'name' => 'Times New Roman'));
        $section->addTextBreak(1);

        return $section;
    }

    /**
     * Profile edit user Category
     * @throws Exception
     */
    public function updateUserInterests(): Response
    {
        /** @var UserService $userService */
        $userService = $this->getSlx()->getUserService();
        $request = $this->getRequest();
        /** Get the user id from the actual user if he's logged or from request if he is registering  */
        $userInterests = $request->get('userInterests');
        /** @var User $oldData */
        $user = $this->getUser();
        $user->setHasInterests(true);
        $user->setInterests(implode(',', $userInterests));
        $res = $userService->updateUserInterests($user, implode(',', $userInterests));
        /** If result is success update user in session */
        if ($res['success']) {
            $this->getSession()->set('userInterest', true);
            /** Update user in the session */
            $userService->saveUserIntoSession($user);
            /** Return success true */
            return $this->renderJson(['success' => true]);
        }

        return $this->renderJson(['success' => false, 'message' => 'invalid request']);

    }

    /**
     * @return Response
     * @throws Exception
     */
    public function identifyVerification()
    {
        $this->redirectFirstPage();
        $user = $this->getUser();
        /** @var SlxWebService $slxService */
        $slxService = $this->getSlx()->getSlxWebService();
        /** Get institutions list from user country */
        $institutions = [];
        if ($user->getCountry()) {
            $institutions = $slxService->getInstitutions($this->fixCountryName($user->getCountry()));
        }
        /** Create professions array */
        $professions = [];
        /** @var array $category */
        foreach ($this->getCategories() as $category) {
            if (isset($category['category'])) {
                $professions[$category['category']] = $category['category'];
            }
        }
        return $this->render('@UserBundle/profile/identify-verification.html.twig', [
            'institutions' => $institutions,
            'professions' => $professions,
            'user' => $user]);
    }

    /**
     * @return null|Response
     * @throws Exception
     */
    public function verifyAccount(): ?Response
    {

        $request = $this->getRequest();
        $user = $this->getUser();
        /** @var UserService $userService */
        $userService = $this->getSlx()->getUserService();
        $institutions = $request->get('institutions');
        $url = $request->get('url');
        foreach ($institutions as $key => $institution) {
            /** Set users institutions */
            $newInstitution = new Institution();
            $newInstitution->setInstitutionId($institution['institutionId']);
            $newInstitution->setInstitutionName($institution['institutionName']);
            $newInstitution->setProfession($institution['programName']);
            $key == 0 ? $newInstitution->setIsPrimary(true) : true;
            /** Add user institution */
            $institutionResult = $userService->addUserInstitution($user->getUserId(), $newInstitution);
            $institutionResult['success'] ? $user->addInstitution($newInstitution) : true;
        }
        /** Set user url if not null */
        if ($url != null) {
            $user->setUrl($request->get('url'));
            $res = $userService->updateUser($user);
            /** Update user in the session */
            $userService->saveUserIntoSession($user);
            /** If result is success update user in session */
            if ($res['success'] && $institutionResult['success']) {
                /** Return success true */
                return $this->renderJson(['success' => true]);
            }
        }
        if ($institutionResult['success']) {
            /** Update user in the session */
            $userService->saveUserIntoSession($user);
            /** set user url  */
            return $this->renderJson(['success' => true]);
        }
        /** set user url  */
        return $this->renderJson(['success' => false]);
    }


    public function removeReadingList()
    {
        $request = $this->getRequest();
        $readingListID = $request->get('readingListID');
        $userId = $this->getUser()->getUserId();
        /** @var ReadingListService $readingListService */
        $readingListService = $this->getSlx()->getReadingListService();
        return $readingListService->deleteReadingList($userId, $readingListID);

    }

    /**
     * @return Response
     * @throws Exception
     */
    public function updateListName(): Response{

        $request = $this->getRequest();
        $readingListID = $request->get('readingListID');
        $listName = $request->get('listName');
        if ($this->getUser()) {
            $userId = $this->getUser()->getUserId();
            $readingListService = $this->getSlx()->getReadingListService();
            $result =$readingListService->setReadingListName($userId, $readingListID,$listName);
            return $this->renderJson(['success' => $result['Result']]);
        } else {
            return $this->renderJson(['success' => false]);
        }

    }

    /**
     * make notification as read
     * @return Response
     * @throws Exception
     */
    public function updateNotification() {

        /** @var SlxWebService $slxWebService */
        $slxWebService = $this->getSlx()->getSlxWebService();
        /** Delete all notifications */
        $slxWebService->updateUserNotification($this->getUser()->getUserId());
        return $this->renderJson(['success' => true]);
    }

    /**
     * return modal of lists
     * @return Response|string
     * @throws Exception
     */
    public function getAllowedReadingList()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $isbn = $request->get('isbn');
        if ($this->getUser()) {
            $userId = $this->getUser()->getUserId();
            /** @var ReadingListService $readingListService */
            $readingListService = $this->getSlx()->getReadingListService();
            $readingLists = $readingListService->getReadingLists($userId, null);
            $readingListsBook = $readingListService->getReadingListsItem($userId, $isbn);
            return $this->render('@MainBundle/modal/add-book-modal.html.twig', [
                'readingLists' => $readingLists,
                'exists' => $readingListsBook,
            ]);
        } else {
            return '';
        }
    }

    /**
     * recommendations user page
     * @throws Exception
     */
    public function recommendations()
    {
        return $this->render('@UserBundle/profile/user-interests.html.twig', ['user'=> $this->getUser()]);
    }
}
