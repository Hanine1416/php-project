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

namespace MainBundle\Controller;

use Doctrine\ORM\EntityManager;
use lib\Config;
use MainBundle\Entity\BooksNew;
use MainBundle\Entity\Contact;
use MainBundle\Entity\Cover;
use MainBundle\Form\ContactUsType;
use MainBundle\Services\BookService;
use MainBundle\Services\ReadingListService;
use MBComponents\Controller\Controller;
use MBComponents\HttpFoundation\Request;
use MBComponents\Services\AppService;
use MBComponents\Services\GoogleReCaptcha;
use MBComponents\Services\SlxWebService;
use Symfony\Component\Form\Form;
use UserBundle\Entity\Banner;
use UserBundle\Entity\CookiePage;
use UserBundle\Entity\CookieTable;
use UserBundle\Entity\RequestRegister;

/**
 * Class MainController
 * @package MainBundle\Controller
 */
class MainController extends Controller
{
    /**
     * Home page with featured book in it
     * @throws \Exception
     */
    public function home()
    {
        $this->redirectFirstPage();
        /** Get site region */
        $reg = $this->region;
        /** Get site language */
        $lang = $this->language;
        /** @var RequestRegister $existentUser */
        $existentUser = null;
        $user = null;
        if ($lang == "en" && $reg == "1"){
            $coverLang = "anz";
        }
        elseif ($reg == "5"){
            $coverLang = "row";
        }
        else{
            $coverLang = $lang;
        }
        $institutions = [];
        $professions = [];
        /** @var BookService $bookService */
        $bookService = $this->getSlx()->getBookService();
        /** @var BookService $bookService */
        $readingListService = $this->getSlx()->getReadingListService();
        /** If the user is logged in then get recommended books */
        $readingLists = null;
        $recommendedBooks =  [];
        $sharedBooks = [];
        $numberOfRecordsShared = 0;
        $numberOfRecommendations = 0;
        $tag = 'like';
        if ($this->isLoggedIn()) {
            $userId = $this->getUser()->getUserId();
            $user = $this->getUser();
            if(isset($_GET['tag'])){
                $tag = $this->cleanMe($_GET['tag']);
                /** @var SlxWebService $slxWebService */
                $slxWebService = $this->getSlx()->getSlxWebService();
                /** Delete all notifications */
                $slxWebService->updateUserNotification($user->getUserId());
            }
            $em = $this->getEntityManager();
            $existentUser = $em->getRepository(RequestRegister::class)->findOneBy(['email'=>$user->getEmail()]);
            //if new user and dan't have interest
            if(!$user->getHasInterests()) {
                //redirect user to page of use categories
                $this->redirect(
                    $this->generateUrl('registration-completed', ['lang' => $this->language, 'reg' => $this->region])
                );
            } else {
                $readingLists = $readingListService->getReadingList($userId);
                /** Get all recommended books from slx */
                [$recommendedBooks, $numberOfRecommendations] = $bookService->getRecommendedBooks($this->region, $lang, $userId,1,10);
                [$sharedBooks, $numberOfRecordsShared]  = $bookService->getSharedBooks($lang, $userId,1,10);
            }
            /** @var SlxWebService $slxService */
            $slxService = $this->getSlx()->getSlxWebService();
            if ($user->getCountry()) {
                $institutions = $slxService->getInstitutions($this->fixCountryName($user->getCountry()));
            }
            /** @var array $category */
            foreach ($this->getCategories() as $category) {
                if (isset($category['category'])) {
                    $professions[$category['category']] = $category['category'];
                }
            }
        }
        /** Get cover book in ASC order  */
        if ($reg == 5){
            $covers = $this->getEntityManager()->getRepository(Cover::class)->findBy(['language' => 'us', 'category' => 'st'], ['position' => 'ASC']);
        }
        else{
            $covers = $this->getEntityManager()->getRepository(Cover::class)->findBy(['language' => $coverLang], ['position' => 'ASC']);
        }

        /** get Books new by lang */
        $booksNew = $this->getEntityManager()
            ->getRepository(BooksNew::class)
            ->findBy(['language' => $coverLang], ['position' => 'ASC']);

        /** @var BookService $bookService */
        $bookService = $this->getSlx()->getBookService();
        $newBooks = [];
        foreach($booksNew as $bookDetails){
            /** Get book information */
            $book = $bookService->getBookDetailsByISBN($bookDetails->getIsbn());
            if ($book)
                array_push($newBooks, $book);
        }
        $isbnReview = '9780323760232';
        $siteLang = $this->getApp()->session->get('site-lang');
        if ($reg == 4){
            $isbnReview = '9780323793742';
        }
        elseif ($siteLang == 'es' ){
            $isbnReview = '9788413823812';
        }
        elseif ($siteLang == 'fr'){
            $isbnReview = '9782294769207';
        }
        elseif ($siteLang == 'de'){
            $isbnReview = '9783437281457';
        }
        $bookReview = $bookService->getBookDetailsByISBN($isbnReview);

        /** Return home view */
        return $this->render(
            '@MainBundle/home.html.twig',
            ['recommendedBooks' => $recommendedBooks,
                'allBooksCount' => count($recommendedBooks),
                'language' => $lang,
                'readingLists' => $readingLists,
                'covers' => $covers,
                'userPhase' => $existentUser ? $existentUser->getMyIcGuide() : null,
                'user' => $user,
                'professions'=> $professions,
                'institutions'=> $institutions,
                'sharedBooks' => $sharedBooks,
                'numberOfRecordsShared' => $numberOfRecordsShared,
                'numberOfRecommendations' => $numberOfRecommendations,
                'tag' => $tag,
                'newBooks' => $newBooks,
                'book' => $bookReview,
            ]
        );
    }

    /**
     * Contact us page with populated user information into the form if logged in
     * @param null $isbn
     * @return \Slim\Http\Response
     * @throws \Exception
     */
    public function contactUs($isbn=null)
    {
        $user = null;
        $educationConsultantEmail = null;
        $userCountry = null;
        /** @var Request $request */
        $request = $this->getRequest();
        $slxService = $this->getSlx()->getSlxWebService();
        /** @var array $countries */
        $contact = new Contact();
        /** Get site language */
        $lang = $this->language;
        /** Get list of available countries */
        $countries = $slxService->getCountries($lang, $this->region);
        $options = ['countries' => $countries, 'translator' => $this->getTranslator(),'isbn'=>null];
        /** test if isbn of book exists add isbn to options to populate subject and description  */
        if ($isbn != null && $this->getSlx()->getBookService()->getBookExistsISBN($isbn)==true){
                $options['isbn'] = $isbn;
        }
        /** If the user is logged in then fill the contact form with his information */
        if ($this->isLoggedIn()) {
            $user = $this->getUser();
            /** Check if the user is affected to an education consultant therefor get his email */
            if (strlen($user->getAmEmail()) > 0) {
                $educationConsultantEmail = $user->getAmEmail();
            }
            /** Bind user info to contact */
            $contact->setEmail($user->getEmail());
            $contact->setName($user->getFullName());
            $contact->setPhone($user->getMainPhone());
            $userCountry = $user->getCountry();
            $contact->setCountry($userCountry);
            /** Bind user with primary institution name if he has one  */
            if ($user->getPrimaryInstitution()) {
                $contact->setInstitution($user->getPrimaryInstitution()->getInstitutionName());
            }
        }
        /** Create contact us form */
        /** @var Form $form */
        $form = $this->createForm(
            ContactUsType::class,
            $contact,
            $options
        );
        $form->handleRequest($request);
        /** Check if the user submitted the form & is it valid */
        if ($form->isSubmitted() && $form->isValid()
              && $this->recaptchaIsValid($request->get('g-recaptcha-response'))) {
            /** Determine support email address by country selected */
            switch ($contact->getCountry()) {
                case 'India':
                    $supportEmail = $this->trans('contact_us.support_email', [], null, 'in');
                    break;
                case 'Bangladesh':
                    $supportEmail = $this->trans('contact_us.support_email', [], null, 'in');
                    break;
                case 'España':
                    $supportEmail = $this->trans('contact_us.support_email', [], null, 'es');
                    break;
                default:
                    $supportEmail = $this->trans(
                        'contact_us.support_email',
                        [],
                        null,
                        $lang == 'fr' ? 'fr' : ($lang == 'de' ? 'de' : ($lang=='us'?'us':'en'))
                    );
                    break;
            }
            if ($contact->getSubject() == $this->trans('contact_us.form.subject_choices.registration_issue') && $lang=='fr') {
                $supportEmail = Config::read('customerServiceEmail')['fr'];
            }
            if ( ($contact->getSubject() == $this->trans('contact_us.form.subject_choices.registration_issue') || $contact->getSubject() == $this->trans('contact_us.form.subject_choices.login_issue')) && $this->region==1 ) {
                $this->isLoggedIn()? $emailSentTo = $educationConsultantEmail : $emailSentTo= $this->trans('contact_us.not_logged_in_email', [], null, 'anz');
                $emailParams = [
                    'subject' => $contact->getSubject(),
                    'email' => $emailSentTo,
                    'cc'=> $this->trans('contact_us.business_email', [], null, 'anz'),
                    'emailContent' => $this->renderView(
                        '@MainBundle/mail/contact-mail.html.twig',
                        ['contact' => $contact]
                    )];
            }
                /** If the subject is help health resources */
            if ($contact->getSubject() == $this->trans('contact_us.form.subject_choices.heath_resource')) {
                if ($this->isLoggedIn()) {
                     $emailSentTo = $educationConsultantEmail;
                }else{
                    if ($this->region == 1) {
                        {
                            $emailSentTo = $this->trans('contact_us.not_logged_in_email', [], null, 'anz');
                            $emailCc['cc'] =  $this->trans('contact_us.business_email', [], null, 'anz');
                        }
                    }  else {  $emailSentTo= $supportEmail; }
                }
                $emailParams = [
                    'subject' => $contact->getSubject(),
                    'email' => $emailSentTo,
                    'emailContent' => $this->renderView(
                        '@MainBundle/mail/contact-mail.html.twig',
                        ['contact' => $contact]
                    )];
                isset($emailCc) ? $emailParams=array_merge($emailParams,$emailCc) : null;
             }
             elseif ($contact->getSubject() == $this->trans('contact_us.form.subject_choices.stem_resource'))
            {
                if ( $this->region == 1) {
                    if ($this->isLoggedIn()) {
                        $emailSentTo = $educationConsultantEmail;
                    } else {
                        $emailSentTo = $this->trans('contact_us.not_logged_in_email', [], null, 'anz');
                        $emailCc['cc'] =  $this->trans('contact_us.business_email', [], null, 'anz');
                    }
                } else {
                    $emailSentTo = $this->trans('contact_us.stem_email');
                }
                /** If the subject is help STEM resources */
                $emailParams = [
                    'subject' => $contact->getSubject(),
                    'email' => $emailSentTo,
                    'emailContent' => $this->renderView(
                        '@MainBundle/mail/contact-mail.html.twig',
                        ['contact' => $contact]
                    )];
                isset($emailCc) ? $emailParams=array_merge($emailParams,$emailCc) : null;
            }
            elseif ($contact->getSubject() == $this->trans('contact_us.form.subject_choices.where_book'))
            {
                /** If the subject is where is my book then send email to EC & TradeOrders */
                $emailParams = [
                    'subject' => $contact->getSubject(),
                    'emailContent' => $this->renderView(
                        '@MainBundle/mail/contact-mail.html.twig',
                        ['contact' => $contact]
                    )];
                /** If the user is in prod environment send query to the customer service */
                $emailParams['email'] = Config::read('customerServiceEmail')[$lang];
                /** Send the EC email exist then send a cc */
                if ($educationConsultantEmail)
                {
                    $emailParams['cc'] = $educationConsultantEmail;
                }
                /** If the subject is other then send mail to education consultant */
            }
            else if ($contact->getSubject() == $this->trans('contact_us.form.subject_choices.other') && $user)
            {
                $emailParams = [
                    'subject' => $contact->getSubject(),
                    'email' => [$educationConsultantEmail,$supportEmail],
                    'emailContent' => $this->renderView(
                        '@MainBundle/mail/contact-mail.html.twig',
                        ['contact' => $contact]
                    )];
            /** Else send the email to the support Email */
            }
            else if ($this->region !=1) {
                $emailParams = [
                    'subject' => $contact->getSubject(),
                    'email' => $supportEmail,
                    'emailContent' => $this->renderView(
                        '@MainBundle/mail/contact-mail.html.twig',
                        ['contact' => $contact]
                    )];
            }
            else {
                /** Do nothing We covered all possibilities */
            }

            if ($contact->getSubject() ==  $this->trans('contact_us.form.subject_choices.digital_not_available') )
            {
                /** If the subject is digital not available */
                $emailParams = [
                    'subject' => $contact->getSubject(),
                    'email' => $this->trans('contact_us.business_email', [], null, 'anz'),
                    'cc' => $educationConsultantEmail,
                    'emailContent' => $this->renderView(
                        '@MainBundle/mail/contact-mail.html.twig',
                        ['contact' => $contact]
                    )];
            }

            /** Send email with the right params  */
            $this->getApp()->mailer->sendEmail($emailParams, Config::read('environment') != 'prod');
            /** Send email to confirm that this contact request is done */
            $this->getApp()->mailer->sendEmail([
                'subject' => $this->trans('contact_us.mailer.confirm_subject'),
                'email' => $contact->getEmail(),
                'emailContent' => $this->renderView('@MainBundle/mail/contact-confirm-mail.html.twig')
            ]);
            $this->redirect($this->generateUrl('main'));
        }
        $view = $form->createView();
        if ($user) {
            /** Bind user country to contact form */
            $view->children['country']->vars['value'] = $userCountry;
            /** Bind user institution to contact form */
            $view->children['institution']->vars['attr']['data-preselect-id'] = $user->getInstitutionId();
            $view->children['institution']->vars['attr']['data-preselect-name'] = $user->getInstitutionName();
        }
        return $this->render('@MainBundle/contact-us.html.twig', ['form' => $view]);
    }

    /**
     * Cookie page that list all cookie that we are using in the site & their information and what used for
     * @throws \Exception
     */
    public function cookiePage()
    {
        /** Get site region */
        $reg = $this->region;
        /** Get site language */
        $lang = $this->language;
        $cookiesLang = $lang == "en" && $reg == "1" ? "anz" : $lang;
        /** @var EntityManager $em */
        $em = $this->getEntityManager();
        /** @var CookiePage $cookiePage */
        $cookiePage = $em->getRepository(CookiePage::class)->findOneBy(['language' => $cookiesLang]);
        /** @var CookieTable $cookieTables */
        $cookieTables = $em->getRepository(CookieTable::class)->findBy(['language' => $cookiesLang, 'enable' => true]);
        $tableElements = $cookiePage ? $cookiePage->getTableElements() : null;
        return $this->render(
            '@MainBundle/cookie.html.twig',
            ['cookiePage' => $cookiePage, 'cookieTables' => $cookieTables, 'tableElements' => $tableElements]
        );
    }

    /**
     * Return list of institutes for specific country
     * @throws \Exception
     */
    public function getInstitutions()
    {
        $request = $this->getRequest();
        if ($request->request->has('country')) {
            $country = $request->get('country');
            /** Temporary fix for country translation not returning data */
            $country = $this->fixCountryName($country);
            /** Get city of request */
            $city = $request->request->has('city') ? $request->get('city') : "";
            $zipCode = $request->request->has('zipCode') ? $request->get('zipCode') : "";
            /** Get state */
            $state = $request->request->has('state') ? $request->get('state') : "";
            $slxService = $this->getSlx()->getSlxWebService();
            $institutions = $slxService->getInstitutions($country, $city, $zipCode,$state);
            return $this->renderJson(['results' => $institutions, 'status' => count($institutions) > 0]);
        }
        return $this->renderJson(['results' => [], 'status' => false]);
    }

    /**
     * Return list of department for specific institution
     * @throws \Exception
     */
    public function getDepartments()
    {
        $request = $this->getRequest();
        if ($request->request->has('institution')) {
            /** Get institution from request */
            $institution = $request->get('institution');
            /** @var SlxWebService $slxService */
            $slxService = $this->getSlx()->getSlxWebService();
            /** Get profession from the request if exist */
            $profession = $request->request->has('profession') ? $request->get('profession') : "";
            /** Get institution departments */
            $departments = $slxService->getDepartments($institution, $profession);
            /** Return departments and if there status */
            return $this->renderJson(['results' => $departments, 'status' => count($departments) > 0]);
        }
        return $this->renderJson(['results' => [], 'status' => false]);
    }

    /**
     * Return list of countries for current site language
     * @return string
     * @throws \Exception
     */
    public function getCountries()
    {
        /** @var SlxWebService $slxService */
        $slxService = $this->getSlx()->getSlxWebService();
        /** @var array $countries */
        $countries = $slxService->getCountries($this->language,$this->region);
        /** Return countries array and there status */
        return $this->renderJson(['results' => $countries, 'status' => count($countries) > 0]);
    }

    /**
     * Return list of cities for a specific country
     * @param $country
     * @return string
     * @throws \Exception
     */
    public function getCities(string $country)
    {
        /** @var SlxWebService $slxService */
        $slxService = $this->getSlx()->getSlxWebService();
        /** @var array $cities */
        $cities = $slxService->getCities($country,strtolower($country)!=='india');
        /** Return cities and there status */
        return $this->renderJson(['results' => $cities, 'status' => count($cities) > 0]);
    }

    /**
     * Return list of states for a specific country
     * @param $country
     * @return string
     * @throws \Exception
     */
    public function getStates(string $country)
    {
        /** @var SlxWebService $slxService */
        $slxService = $this->getSlx()->getSlxWebService();
        /** @var array $states */
        $states = $slxService->getStates($country,$this->region,$this->language);
        /** Return states and there status */
        return $this->renderJson(['results' => $states, 'status' => count($states) > 0]);
    }

    /**
     * Return list of specialities for a specific profession
     * @throws \Exception
     */
    public function getSpecialities()
    {
        $request = $this->getRequest();
        if ($request->request->has('profession'))
        {
            /** Get profession from request */
            $profession = $request->get('profession');
            /** @var SlxWebService $slxService */
            $slxService = $this->getSlx()->getSlxWebService();
            /** Get specialities from profession and language */
            $specialities = $slxService->getSpecialities($profession, $this->language);
            /** Return specialities and there status  */
            return $this->renderJson(['results' => $specialities, 'status' => count($specialities) > 0]);
        }
        /** Return false if request don't have profession */
        return $this->renderJson(['results' => [], 'status' => false]);
    }

    /**
     * Return department's addresses
     * @throws \Exception
     */
    public function getAddresses()
    {
        $request = $this->getRequest();
        /** check if the request is done by ajax call and contain the parameter department */
        if ($request->request->has('department')) {
            /** Get department Id from request */
            $departmentId = $request->get('department');
            /** @var SlxWebService $slxService */
            $slxService = $this->getSlx()->getSlxWebService();
            /** Get addresses from department Id */
            $addresses = $slxService->getAddress($departmentId);
            /** Return addresses and there status */
            return $this->renderJson(['results' => $addresses, 'status' => count($addresses) > 0]);
        }
        return $this->renderJson(['results' => [], 'status' => false]);
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

    /** Check if cep is valid from external cep validation api
     * @param string $cep
     * @return \Slim\Http\Response
     * @throws \Exception
     */
    public function getAddressFromCep(string $cep)
    {
        /** @var AppService $appService */
        $appService = $this->getApp()->getService(AppService::class);
        /** Return address from CEP */
        return $this->renderJson($appService->getAddressFromCep($cep));
    }

    /**
     * Save feedback  from the home page
     * @return \Slim\Http\Response
     * @throws \Exception
     */
    public function giveFeedback()
    {
        $request = $this->getRequest();
        if ($request->get('feedback') != '' && $this->recaptchaIsValid($request->get('g-recaptcha-response'))) {
            $userId = "";
            if ($this->isLoggedIn())
            {
                $userId = $this->getUser()->getUserId();
            }
            /** @var SlxWebService $slxService */
            $slxService = $this->getSlx()->getSlxWebService();
            /** Get addresses from department Id */
            $feed = $slxService->saveFeedback($request->get('feedback'),$userId);
            return $this->renderJson(['results' => $feed, 'status' => $feed]);
        }
        return $this->renderJson(['results' => true, 'status' => true]);
    }

    /**
     * @return \Slim\Http\Response
     * @throws \Exception
     */
    public function addBookReadingList() {
        $readingLists= $this->getRequest()->get('readingLists');
        $isbn = $this->getRequest()->get('isbn');
        $title = $this->getRequest()->get('bookTitle');

        $details = "<span class='title-history'>".$title."</span>";
        $action = $this->getRequest()->get('action');
        if(isset($action)) {
            $listName = $this->getRequest()->get('listName');
            if(isset($listName) && $action == 'copy') {
                $details = $details." was copied from ".$listName." list";
            } else if(isset($listName) && $action == 'move') {
                $details = $details." was moved from ".$listName." list";
            }
        } else {
            $details = $details." was added to the list";
        }
        $section = $this->getRequest()->get('section');
        $result= null;
        /** @var ReadingListService $readingListService */
        $readingListService = $this->getSlx()->getReadingListService();
        foreach ( $readingLists as $readingList) {
            $result = $readingListService->setBookReadingList($this->getUser()->getUserId(), $isbn, $readingList['id'], $section);
            $readingListService->saveHistory($this->getUser()->getUserId(), $readingList['id'], $details);
        }
        if ($result['Result'])
        {
            return $this->renderJson(['success' => true]);
        }
        return $this->renderJson(['success' => false, 'message' => $result['reply']]);

    }

    /**
     * @return \Slim\Http\Response
     * @throws \Exception
     */
    public function loadMoreRecommended() {
        $request = $this->getRequest();
        /** Get site language */
        $lang = $this->language;
        /** @var BookService $bookService */
        $bookService = $this->getSlx()->getBookService();
        $start = $request->get('start');
        $isMobile = $request->get('isMobile');
        /** If the user is logged in then get recommended books */
        $user = $this->getUser();
        $userId = $user->getUserId();
        [$recommendedBooks, $numberOfRecommendations] = $bookService->getRecommendedBooks($this->region, $lang, $userId,$start,10);
        return $this->render('@MainBundle/parts/load_more_ajax.html.twig',
            [   'recommendedBooks' => $recommendedBooks,
                'language' => $lang,
                'isMobile' => filter_var($isMobile, FILTER_VALIDATE_BOOLEAN),
                'user' => $user,
                'numberOfRecommendations' => $numberOfRecommendations
            ]
        );
    }

    /**
     * load more shared books
     * @return \Slim\Http\Response
     * @throws \Exception
     */
    public function loadMoreSharedBook() {
        $request = $this->getRequest();
        /** Get site language */
        $lang = $this->language;
        /** @var BookService $bookService */
        $bookService = $this->getSlx()->getBookService();
        $start = $request->get('start');
        $isMobile = $request->get('isMobile');
        /** If the user is logged in then get recommended books */
        $user = $this->getUser();
        if($user) {
            $userId = $user->getUserId();
            [$sharedBooks, $numberOfRecords] = $bookService->getSharedBooks($lang, $userId,$start,10);
            return $this->render('@MainBundle/parts/load_more_shared_book_ajax.html.twig',
                [   'sharedBooks' => $sharedBooks,
                    'language' => $lang,
                    'isMobile' => filter_var($isMobile, FILTER_VALIDATE_BOOLEAN),
                    'user' => $user,
                    'numberOfRecords' =>$numberOfRecords
                ]
            );
        } else {
            return '';
        }

    }

    /**
     * @return \Slim\Http\Response
     * @throws \Exception
     */
    public function showFeatures(){
        return $this->render('@MainBundle/features.html.twig', [

        ]);
    }

    /**
     * Edit language and catalog
     * @return \Slim\Http\Response
     * @throws \Exception
     */
    public function languageCatalog(){

        $request = $this->getRequest();
        if ($request->isMethod('POST')) {
            $lang = $request->request->get('lang');
            $cat = $request->request->get('cat');
            $this->getApp()->session->set('lang', $lang);
            return true;
        }
        return false;
    }

    /**
     * This function return list of books according the search value given from the search input
     * @return \Slim\Http\Response
     * @throws \Exception
     */
    public function showSearchBar()
    {
        $request = $this->getRequest();
        $books = $this->getApp()->session->get('searchBook');
        //val is the value that i get it from the search input
        $word = strtolower($request->request->get('val'));
        $res = [];
        $count = 0;
        if ($request->isMethod('POST')) {
            foreach ($books as $key => $val) {
                //str_contains is a php 8 function but it supported by symfony better then strops
                if ((str_contains(strtolower($val['title']), $word)) || (str_contains(strtolower($val['author']), $word)) || (str_contains($val['isbn'], $word))){
                    if(!in_array($val, $res, true))
                    {
                        array_push($res, $val);
                        $count ++;
                        if ($count == 6){
                            break;
                        }
                    }
                }
            }
            return $this->renderJson(['success' => true, 'message' => $res]);
        }
        return $this->renderJson(['success' => false, 'message' => 'no books to show']);
    }

    /**
     * use this function to save book according to the current language and catalog to use it in the search bar
     * @throws \Exception
     */
    public function saveSessionBooks(): \Slim\Http\Response
    {
        if ($this->getApp()->session->get('searchBook') == null)
        {
            /** set session with book for the current region */
            $books = $this->getSlx()->getBookService()->searchBooks(
                [],
                'date-desc',
                '',
                'description',
                null,
                $this->language,
                $this->region);
             $searchBooks = [];
            foreach ($books[0] as $key => $value){
                array_push($searchBooks, [
                    'title' => $value['title'],
                    'image' => Config::read('pathimageCDN').'/'.$value['isbn'].'.jpg',
                    'author' => $value['author'],
                    'isbn' => $value['isbn']
                ]);
            }
            $this->getApp()->session->set('searchBook', $searchBooks);
            /** end code of book session */
            return $this->renderJson(['results' => [], 'status' => true]);
        }
        return $this->renderJson(['results' => [], 'status' => false]);
    }

    /**
     * clear books session when we switch catalog to get new data
     * @return void
     */
    public function clearBooksSession() {
        $this->getApp()->session->remove('searchBook');
    }

}
