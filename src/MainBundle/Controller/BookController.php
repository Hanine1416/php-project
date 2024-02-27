<?php

/*
 * This file is part of the Inspection Copy.
 * Copyright (C) 2019 Elsevier.
 * Created by mobelite.
 *
 * Date: 4/11/18
 * Time: 16:28
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace MainBundle\Controller;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use lib\Config;
use MainBundle\Entity\BookInstitutionRequest;
use Exception;
use MainBundle\Entity\BookRequest;
use MainBundle\Form\BookRequestType;
use MainBundle\Services\BookService;
use MainBundle\Services\CalameoService;
use MBComponents\Controller\Controller;
use MBComponents\Exceptions\NotFoundException;
use MBComponents\HttpFoundation\Session;
use MBComponents\Services\SlxWebService;
use Symfony\Component\HttpFoundation\Response;
use UserBundle\Entity\Address;
use UserBundle\Entity\Code;
use UserBundle\Entity\Institution;
use UserBundle\Entity\RequestRegister;
use UserBundle\Entity\User;
use UserBundle\Form\AddressType;
use UserBundle\Form\InstitutionType;
use UserBundle\Services\UserService;

/**
 * Class BookController
 * @package MainBundle\Controller
 */
class BookController extends Controller
{
    /**
     * Show book details page
     * @param $isbn
     * @return \Slim\Http\Response
     * @throws NotFoundException
     * @throws Exception
     */
    public function showBookDetail(string $isbn): \Slim\Http\Response
    {
        $this->redirectFirstPage();
        /** @var BookService $bookService */
        $bookService = $this->getSlx()->getBookService();
        /** Get book information */
        $book = $bookService->getBookDetailsByISBN($isbn);

        /** @var array $countries */
        $countries = null;
        /** Get catalogue language */
        $catalogueLang = $this->getApp()->session->get('lang');
        /** Get site language */
        $siteLang = $this->getApp()->session->get('site-lang') ?? $catalogueLang;
        /** Get all related books */
        $relatedBook = $bookService->getBooksBySubCategories(
            $book->getSubCategory(),
            $this->region,
            $catalogueLang,
            true,
            $book->getIsbn()
        );
        /** Get available currency for current region */
        $currencyRegion = $bookService->getCurrencyRegion($this->region);
        /** Get courses list */
        $coursesList = $bookService->getCoursesList($this->region, $this->language);
        /** Get courses level */
        $courseLevels = $bookService->getCourseLevels($this->region, $siteLang);
        /** Reading List Service */
        $readingListService = $this->getSlx()->getReadingListService();
        /** Create add institution form */
        $newInstitutionForm = null;
        $institutions = null;
        $addressForm = null;
        $professions = [];
        $readingLists = null ;
        $feedBacks = null;
        if ($this->isLoggedIn())
        {
            /** Get institutions by user country */
            $institutions = $this->getSlx()->getSlxWebService()->getInstitutions(
                $this->fixCountryName($this->getUser()->getCountry())
            );
            foreach ($this->getCategories() as $category)
            {
                if (isset($category['category'])) {
                    $professions[$category['category']] = $category['category'];
                }
            }
            /** Create new institution form */
            $newInstitutionForm = $this->createForm(
                InstitutionType::class,
                new Institution(),
                ['professions' => $professions, 'institutions' => $institutions]
            );
            $newInstitutionForm = $newInstitutionForm->createView();
            /** Create add address form */
            $user = $this->getUser();
            /** @var Address $address */
            $newAddress = new Address();
            /** @var SlxWebService $slxService */
            $slxService = $this->getSlx()->getSlxWebService();
            /** Load countries and user country's institutions */
            $countries = $slxService->getCountries($this->language, $this->region);
            /** Get User primary institution  */
            /** @var  $primaryInstitution */
            $primaryInstitution = $user->getPrimaryInstitution();
            /** Populate new user address form with data from primary institution */
            if ($primaryInstitution && $primaryInstitution->getDepartmentId())
            {
                /** Populate addresses input */
                $addressInfo = $slxService->getAddress($primaryInstitution->getDepartmentId());
                $newAddress = $this->setAddressInfos($addressInfo,$newAddress);
            }
            else if ($primaryInstitution)
            {
                /** Set addresses for primary institution */
                $newAddress->setAddress2($primaryInstitution->getInstitutionName());
                $primaryInstitution->getDepartmentName() != null ? $newAddress->setAddress3($primaryInstitution->getDepartmentName()): false;
            } else {
                // SonarQube rule
            }

            /** Populate address with phone number from user personal details */
            $newAddress->setPhone($user->getMobile());
            /** Create address form */
            $addressForm = $this->createForm(
                AddressType::class,
                $newAddress,
                ['language' => $this->getSession()->get('site-lang'), 'countries' => $countries]
            );
            $addressForm = $addressForm->createView();
            $readingLists = $readingListService->getReadingList($user->getUserId());
            //call webservice to save user opened book event only for logged-in users
            $bookService->saveUserEvent($isbn, $user->getUserId(), 'BookAccess');
        }
        
        /** Get book subcategories*/
        $subCategory = $book->getSubCategory();
        $bookCategory = 'hs';
        /** If book has one category S&T it will be an S&T book */
        foreach ($subCategory as $category)
        {
            strpos($category['id'], 'PROMISH') !== false ? $bookCategory = 'hs' : $bookCategory = 'st';
        }
        $sessionBooks = $this->getSession()->get('booksNavigation');
        /** get previous and next book **/
        $next = '';
        $prev = '';
        if(!empty($sessionBooks)) {
            $isbnPosition =  array_search($book->getIsbn(), array_column($sessionBooks, 'isbn'));
            if($isbnPosition == 0 && count($sessionBooks) > 1) {
                $next = $sessionBooks[1];
            }
            if($isbnPosition > 0 && $isbnPosition < array_key_last($sessionBooks)) {
                $prev = $sessionBooks[$isbnPosition-1];
                $next = $sessionBooks[$isbnPosition+1];
            }
            if($isbnPosition > 0 && $isbnPosition == array_key_last($sessionBooks)) {
                $prev = $sessionBooks[$isbnPosition-1];
            }
        }

        /** Get feedBacks of book by isbn */
        $feedBacks = $bookService->getAllBookReviews($this->getUser()? $this->getUser()->getUserId() : null, $isbn);
        $totalrating = 0;

        foreach ($feedBacks as $value) {
            $totalrating += $value->Rating;
        }
        if (count($feedBacks) != 0)
        {
            $totalrating = ($totalrating/(count($feedBacks)));
        }


        $params = [
            'user' => $this->getUser(),
            'institutionForm' => $newInstitutionForm,
            'addressForm' => $addressForm,
            'institutions' => $institutions,
            'countries' => $countries,
            'book' => $book,
            'courses' => $coursesList,
            'levels' => $courseLevels,
            'currencyRegion' => $currencyRegion,
            'bookService' => $bookService,
            'relatedBooks' => $relatedBook,
            'bookCategory' => $bookCategory,
            'professions' => $professions,
            'readingLists' => $readingLists,
            'prevLink' => $prev,
            'nextLink' => $next,
            'totalrating' => $totalrating,
            'feedBacks'=> $feedBacks,
        ];

        /** Read ancillary content from amazon bucket for all books */
        $params['ancillary'] = $this->fetchAncillaryContent($isbn);
        /** Return the view  */
        return $this->render('@MainBundle/book/show-details.html.twig', $params);
    }

    private function fetchAncillaryContent($isbn): array
    {
        $s3 = new S3Client([
            'version' => 'latest',
            'region' => 'eu-west-1',
            'credentials' => array(
                'key' => Config::read('S3_BUCKET_KEY'),
                'secret' => Config::read('S3_BUCKET_SECRET'),
            )
        ]);
        $ancillaryContent = [];
        try {
            $results = $s3->listObjects([
                'Bucket' => 'icancillary',
                'Prefix' => "$isbn/"
            ])->search('Contents');
            if ($results) {
                foreach ($results as $item) {
                    if ($item['Size'] > 0) {
                        $parts = array_slice(explode('/', $item['Key']), 1);
                        $current = &$ancillaryContent;
                        for ($i = 1, $max = count($parts); $i < $max; $i++) {
                            if (!isset($current[$parts[$i - 1]])) {
                                $current[$parts[$i - 1]] = array();
                            }
                            $current = &$current[$parts[$i - 1]];
                        }
                        $cmd = $s3->getCommand('GetObject', [
                            'Bucket' => 'icancillary',
                            'Key' => $item['Key']
                        ]);
                        $request = $s3->createPresignedRequest($cmd, '+300 minutes');
                        $current[] = ['name' => $parts[$i - 1], 'size' => $item['Size'], 'url' => (string)$request->getUri()];
                    }
                }
            }
        } catch (S3Exception $e) {
        }
        return $ancillaryContent;
    }

    /**
     * Request print or digital book
     * @throws Exception
     */
    public function requestBook(): ?\Slim\Http\Response
    {
        /**
         * create new book request object
         * @var BookRequest $bookRequest
         */
        $bookRequest = new BookRequest();
        $bookRequestForm = $this->createForm(BookRequestType::class, $bookRequest);
        $bookRequestForm->handleRequest($this->getRequest());
        $em = $this->getEntityManager();
        /** check if for is submitted & valid */
        $user = $this->getUser();
        if ($bookRequestForm->isSubmitted() && $bookRequestForm->isValid())
        {
            /** @var BookService $bookService */
            $bookService = $this->getSlx()->getBookService();
            /** Create all new institutions  */
            $institutions = $this->getRequest()->request->get('bookRequest')['institutions'];
            $userCountry = $this->getRequest()->request->get('country');
            /** @var UserService $userService */
            $userService = $this->getSlx()->getUserService();
            /** add user country */
            if ($userCountry != '' && $userCountry != "undefined")
            {
                $user->setCountry($userCountry);
                /** Update user basic info */
                $res = $userService->updateUser($user);
                /** If result is success update user in session */
                if ($res['success']) {
                    /** Update user in the session */
                    $userService->saveUserIntoSession($user);
                }
            }
            /** @var RequestRegister $existentUser */
            $existentUser = $em->getRepository(RequestRegister::class)->findOneBy(['email' => $user->getEmail()]);
            if ($existentUser !== null && $existentUser->getMyIcGuide() ==="homepage done")
            {
                /** Set user guide to first request step */
                $existentUser->setMyIcGuide('first request');
                $em->persist($existentUser);
                $em->flush();
            }
            /** add user institutions */
            foreach ($institutions as $k => $institution)
            {
                if ((isset($institution['institutionInstId'])) && strpos($institution['institutionInstId'], 'new_') !== FALSE)
                {
                    /** @var BookInstitutionRequest $bookInstitution */
                    $bookInstitution = $bookRequest->getInstitutions()[$k];
                    /** Create the institution  */
                    /** @var  Institution $newInstitution */
                    $newInstitution = new Institution();
                    $newInstitution->setInstitutionName($institution['institution']);
                    /** Don't send  institutionId when it was new institution created manually*/
                    if (strpos($institution['institutionId'], 'new_') === FALSE)
                    {
                        $newInstitution->setInstitutionId($institution['institutionId']);
                    }
                    /** Set newInstitution info */
                    $newInstitution->setDepartmentName($institution['department']);
                    $newInstitution->setDepartmentId($institution['departmentId']);
                    $newInstitution->setProfession($institution['profession']);
                    $newInstitution->setSpeciality($institution['speciality']);
                    $newInstitution->setIsPrimary($institution['primary']);
                    $newInstitution->setHasRequest(true);
                    /** Add user institution */
                    $result = $userService->addUserInstitution($user->getUserId(), $newInstitution);
                    /** If the new institution has been successfully saved into slx */
                    if ($result['success'])
                    {
                        /** Set new institution ID */
                        $newInstitution->setId($result['reply']);
                        /** Set the new institution ID to the bookInstitution request */
                        $bookInstitution->setInstitutionId($newInstitution->getId());
                        /** Add the new institution to the user institutions*/
                        $user->addInstitution($newInstitution);

                        /** @var Address $address */
                        $newAddress = new Address();
                        /** @var array $newAddressInfo */
                        $newAddressInfo = null;
                        /**
                         * Check if the new institution is primary then update the old primary to be normal
                         * @var Institution $institution
                         */
                        if ($newInstitution->isPrimary())
                        {
                            /** @var Institution $institutions
                             */
                            foreach ($user->getInstitutions() as $institutions)
                            {
                                /** For each institution test if it's the new primary one */
                                if ($institutions->getId() !== $newInstitution->getId())
                                {
                                    /** Set the institution as not primary */
                                    $institutions->setIsPrimary(false);
                                    /** Update user institution */
                                    $user->updateInstitution($institutions);
                                    break;
                                }
                            }
                            if ($newInstitution->getDepartmentId())
                            {
                                /** Populate addresses input */
                                $newAddress = $this->setAddressInfo($newInstitution);
                            }
                            /** Set Institution Name to address2 */
                            $newAddress->setAddress2($newInstitution->getInstitutionName());
                            /** Set Department Name to address3 */
                            $newAddress->setAddress3($newInstitution->getDepartmentName());
                        }
                        /** Re save user into session after institutions modification */
                        $userService->saveUserIntoSession($user);
                    } else {
                        $bookRequest->getInstitutions()->remove($k);
                    }
                }
            }
            if ($bookRequest->getInstitutions()->count() > 0)
            {
                $result = $bookService->requestBook($bookRequest, $user);
                $userService = $this->getSlx()->getUserService();
                /** update user institution status */
                $user->setInstitutions($userService->getUserInstitutions($this->getUser()->getUserId()));
                /** update user address status */
                $user->setAddresses($userService->getUserAddresses($user->getUserId()));
                $userService->saveUserIntoSession($user);
                return $this->renderJson([
                    'success' => $result['Result'],
                    'message' => $result['Reply'],
                    'redirect' => $this->generateUrl('my-personal-details', ['lang' => $this->language, 'reg' => $this->region])]);
            }
        }
        return $this->renderJson([
            'success' => false,
            'message' => 'Something went wrong please refresh your page and try again'
        ]);
    }

    /**
     * Cancel user's book request
     * @throws Exception
     */
    public function cancelBookRequest()
    {
        $bookService = $this->getSlx()->getBookService();
        /** @var User $user */
        $user = $this->getUser();
        /** @var BookService $bookService */

        /** create book request to be able to delete the book request */
        $format = $this->getRequest()->get('format');
        $isbn = $this->getRequest()->get('isbn');
        $preOrder = $this->getRequest()->get('preorder');
        /** Set bookRequest to cancel info */
        $bookRequest = new BookRequest();
        $bookRequest->setBookIsbn($isbn);
        $bookRequest->setBookFormat($format);
        $bookRequest->setPreOrder($preOrder);

        /** call slx WS with given  */
        $res = $bookService->requestBook($bookRequest, $user, 'Cancel');
        return $this->renderJson(['success' => $res['Result'], 'message' => $this->trans('error.nb_1201')]);
    }

    /**
     * Make a book review (adopt it or not) with feedback
     * @throws Exception
     */
    public function reviewBook()
    {
        $user = $this->getUser();
        /** @var BookService $bookService */
        $bookService = $this->getSlx()->getBookService();
        /** get Adoptions */
        $adoptions = $this->getRequest()->get('adoptions');
        /** get feedback */
        $feedBack = $this->getRequest()->get('feedback');
        /** Create adoption for each course review */
        foreach ($adoptions as $adoption)
        {
            $res = $bookService->setBookAdoption($user->getUserId(), $adoption);
            /** Check if adoption succeeded & was a positive review & has feedback */
            if ($adoption['Status'] && $res['Result'] && isset($feedBack)) {
                $feedBack['productId'] = $adoption['ProductId'];
                $feedBack['adoptionId'] = $adoption['AdoptionID'];
                /** Set book feedback */
                $bookService->setBookFeedBack($user->getUserId(), $feedBack);
            }
        }
        return $this->renderJson(['success' => true]);
    }

    /**
     * Set book review and rating
     * @throws Exception
     */
    public function reviewRatingBook(){
        $user = $this->getUser();
        /** @var BookService $bookService */
        $bookService = $this->getSlx()->getBookService();
        /** get feedback */
        $feedBack = $this->getRequest()->get('feedback');
            if (isset($feedBack)) {
                /** Set book feedback */
                $bookService->setBookReview($user, $feedBack);
            }
        return $this->renderJson(['success' => true, 'feedbaks' => $feedBack]);
    }

    /**
     * Set book review and rating
     * @throws Exception
     */
    public function reviewRatingBookAjax(){
        $request = $this->getRequest();
        $isbn = $this->cleanMe($request->query->get('isbn'));
        $user = $this->getUser();
        /** @var BookService $bookService */
        $bookService = $this->getSlx()->getBookService();
        $feedBacks = $bookService->getAllBookReviews($this->getUser()? $this->getUser()->getUserId() : null, $isbn);

        return $this->render('@MainBundle/parts/review.html.twig', [
            'feedBacks' => $feedBacks,
            'isbn' => $isbn
        ]);
    }

    /**
     * Set book review and rating
     * @throws Exception
     */
    public function averageReviewBookAjax(){
        $request = $this->getRequest();
        $isbn = $request->query->get('isbn');

        /** @var BookService $bookService */
        $bookService = $this->getSlx()->getBookService();
        /** Get book information */
        $book = $bookService->getBookDetailsByISBN($isbn);

        return $this->render('@MainBundle/parts/average_reviews.html.twig', [
            'book' => $book,
        ]);
    }


    /**
     * Set like review of book
     * @return \Slim\Http\Response
     * @throws Exception
     */
    public function likeReviewBook() {
        $request = $this->getRequest();
        $feedbackId = $request->get('feedBackId');
        $like  = $request->get('like');
        $user = $this->getUser();
        /** @var BookService $bookService */
        $bookService = $this->getSlx()->getBookService();

        $response = $bookService->setBookReviewLike($user->getUserId(), $feedbackId,$like);
        if ($response['Result']) {
            return $this->renderJson(['success' => true]);
        }
        return $this->renderJson(['success' => false]);

    }


    /**
     * Search book or browse book subcategory
     * @param $catalogue
     * @return \Slim\Http\Response
     * @throws Exception
     */
    public function searchBooks($catalogue): \Slim\Http\Response
    {
        if($this->getUser()){
            //redirect user to put code
            $userCode = $this->getEntityManager()->getRepository(Code::class)->findOneBy(['email' =>$this->getUser()->getEmail()]);
            if ($userCode != null ) {
                $this->redirect(
                    $this->generateUrl('confirm-authentication')
                );
            }

            /** Redirect new user to choose interests */
            if(!$this->getUser()->getHasInterests()){
                $this->redirect(
                    $this->generateUrl('registration-completed', ['lang' => $this->language, 'reg' => $this->region])
                );
            }

        }
        $catalogue = strtolower($catalogue);
        /** @var $request */
        $request = $this->getRequest();
        /** Set sort parameters */
        $sort = $request->get('order', 'date-desc');
        $search = $request->get('s', '');
        $searchBy = $request->get('sb', '');
        $categories = trim($request->get('cat', ''));
        $selectedCategory = $categories;
        $categories = $categories ? explode(';', $categories) : [];
        if (($catalogue === 'hs' || $catalogue === 'st') && empty($categories))
        {
            $categories = $this->getCategoriesByType($catalogue);
        }
        /** If xhr request then return json data */
        if ($this->getRequest()->isMethod('POST'))
        {
            /** @var BookService $bookService */
            $bookService = $this->getSlx()->getBookService();
            [$books, $filters, $userBooks] = $bookService->searchBooks($categories, $sort, $search, $searchBy,
                $this->isLoggedIn() ? $this->getUser()->getUserId() : null);
            return $this->renderJson(['books' => $books, 'filters' => $filters, 'userBooks' => $userBooks]);
        }
        /** Render view */
        return $this->render('@MainBundle/book/books.html.twig', [
            'searchBy' => $searchBy,
            'searchFor' => $search,
            'catalogue' => $catalogue,
            'selectedCategory' => $selectedCategory,
        ]);
    }

    /**
     * Read book from calameo api
     * @param string $isbn
     * @return \Slim\Http\Response
     * @throws NotFoundException
     * @throws Exception
     */
    public function readBook(string $isbn)
    {
        $session = $this->getSession();
        $user = $this->getUser();
        /** @var CalameoService $calameoService */
        $calameoService = new CalameoService($user);
        /** Get user calameo account information */
        $userCalameoInfo = $calameoService->getUserInfo();
        /** Get book information from calameo */
        $bookCalameoInfo = $calameoService->getBookInfo($isbn);
        if ($bookCalameoInfo && $userCalameoInfo)
        {
            $session->set('userInfoCalameo', $userCalameoInfo);
            $session->set('bookIDCalameo', $bookCalameoInfo['bookId']);
            $session->set('bookFlag', 1);

            /** check if the user is using a mobile or a desktop */
            $userAgent = $this->getRequest()->server->get('HTTP_USER_AGENT');
            if (preg_match('/iPad/', $userAgent) ||
                preg_match('/Trident/', $userAgent) ||
                preg_match(
                    '/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|
            hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|
            phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|
            windows (ce|phone)|xda|xiino/i',
                    $userAgent
                ) ||
                preg_match(
                    '/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|
                            ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|
            attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|
            bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|
            da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|
            er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|
            haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|
            hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|
            ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)
            |50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|
            mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|
            10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|
            pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|
            \-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|
            se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|
            sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|
            ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|
            vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|
            wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',
                    substr($userAgent, 0, 4)
                ))
            {
                $url = 'https://v.calameo.com/index.htm?bkcode=' . $bookCalameoInfo['bookId'] .
                    '&authid=' . $bookCalameoInfo['AccountID'] . '&login=' . $userCalameoInfo['login'] . '&password=' .
                    $userCalameoInfo['password'] . '&subid=' . $userCalameoInfo['SubscriptionID'] .
                    '&bgcolor=313639&trackersource=calameo&language=en&langid=en&mobiledirect=1';
                return $this->render(
                    '@MainBundle/book/read-book-mobile.html.twig',
                    ['url' => $url, 'bookTitle' => $bookCalameoInfo['title']]
                );
            }

            $jsSource = $this->generateUrl('book-js-reader', ['timestamp' => microtime(false)]);
            return $this->render(
                '@MainBundle/book/read-book.html.twig',
                ['jsSource' => $jsSource, 'bookTitle' => $bookCalameoInfo['title']]
            );
        }
        throw new NotFoundException('Book with ISBN <b>' . $isbn . '</b> cannot be read');
    }

    /**
     * Return configured script to generate plugin to read book from calameo
     */
    public function bookJsReader(): \Slim\Http\Response
    {
        /** @var Session $session */
        $session = $this->getSession();
        $userInfo = $session->get('userInfoCalameo');
        $bookIsbn = $session->get('bookIDCalameo');
        $bookFlag = $session->get('bookFlag');
        $response = new Response();
        $response->headers->set('Content-Type', 'application/javascript');
        if ($bookFlag == 1)
        {
            $response->setContent('var vc = Calameo.EmbedConfig.ViewerConfig;
            vc.bkcode       = "' . $bookIsbn . '";
            vc.authid       = "' . $userInfo['AccountID'] . '";
            vc.login        = "' . $userInfo['login'] . '";
            vc.password     = "' . $userInfo['password'] . '";
            vc.subid        = "' . $userInfo['SubscriptionID'] . '";
            vc.bgcolor = "313639";
            vc.trackersource = "calameo";

            Calameo.EmbedConfig.ViewerConfig            = vc;
            Calameo.EmbedConfig.MobileDomain            = "5210-000028/js/",
            Calameo.EmbedConfig.ViewerDomain            = "5210-000028/",
            Calameo.EmbedConfig.MobileDirect            = true;
            Calameo.EmbedConfig.UseIFrame               = true;

            var cv = new Calameo.EmbeddedViewer();');
        }
        $this->getApp()->response->setBody($response->getContent());
        return $this->getApp()->response;
    }

    /**
     * Return courses details for a book request
     * @param $productId
     * @return \Slim\Http\Response
     * @throws Exception
     */
    public function getCourseDetails($productId): \Slim\Http\Response
    {
        $user = $this->getUser();
        /** @var BookService $bookService */
        $bookService = $this->getSlx()->getBookService();
        /** Get user requested books */
        $userBookRequests = $bookService->getUserBooks($user->getUserId(), false);
        $courseDetails = null;
        foreach (array_merge($userBookRequests['Approved'], $userBookRequests['Expired']) as $userBookRequest)
        {
            if ($userBookRequest['ProductId'] === $productId) {
                $courseDetails = $userBookRequest['Institutions']['Institution'][0] ?? $userBookRequest['Institutions']['Institution'];
                break;
            }
        }
        if ($courseDetails)
        {
            /** @var Institution $institution */
            foreach ($user->getInstitutions()->toArray() as $institution)
            {
                if ($courseDetails['InstitutionID'] == $institution->getId())
                {
                    $courseDetails['InstitutionName'] = $institution->getInstitutionName();
                    break;
                }
            }
        }
        return $this->renderJson(['success' => $courseDetails !== null, 'courseDetails' => $courseDetails]);
    }

    /**
     * Set Address info
     * @param $addressInfo
     * @param Address $newAddress
     * @param null $countries
     * @return Address $newAddress
     */
    private function setAddressInfos($addressInfo, $newAddress,$countries=null){
        if ($addressInfo)
        {
            /** Set new address info */
            $newAddress->setCity($addressInfo->City);
            $newAddress->setState($addressInfo->State);
            $newAddress->setAddress1($addressInfo->Address1);
            $newAddress->setAddress2($addressInfo->Address2);
            $newAddress->setAddress3($addressInfo->Address3);
            $newAddress->setAddress4($addressInfo->Address4);
            $newAddress->setPostalCode($addressInfo->Postalcode);
            /** Get country of address */
            if ($addressInfo->Country) {
                $newCountry = new \stdClass();
                $newCountry->Text = $addressInfo->Country;
                if ($countries) {
                    array_push($countries, $newCountry);
                } else {
                    $countries[] = $newCountry;
                }
                $newAddress->setCountry($addressInfo->Country);
            }
        }
        return $newAddress;
    }

    /** Return categories name by type (ST or HS)
     * @param string $type
     * @return array
     */
    private function getCategoriesByType(string $type): array
    {
        $categories = ['st' => [], 'hs' => []];
        foreach ($this->categories as $key => $category)
        {
            strpos($key, 'PROMISH') !== false ? $categories['hs'][] = $category['category'] : $categories['st'][] = $category['category'];
        }
        return $categories[$type] ?? [];
    }

    /**
     * Set new Address info
     * @param Institution $newInstitution
     * @return Address
     */
    public function setAddressInfo(Institution $newInstitution)
    {
        /** @var array $countries */
        $countries = null;
        /** @var SlxWebService $slxService */
        try {
            $slxService = $this->getSlx()->getSlxWebService();
        } catch (\Exception $e) {
        }
        /** Load countries and user country's institutions */
        $countries = $slxService->getCountries($this->language,$this->region);
        /** @var Address $address */
        $newAddress = new Address();
        /** Populate addresses input */
        $addressInfo = $slxService->getAddress($newInstitution->getDepartmentId());
        /** If address info exists set address information */
        return $this->setAddressInfos($addressInfo,$newAddress,$countries);
    }

    /**
     * Share book with others users
     * @return \Slim\Http\Response
     * @throws Exception
     */
    public function shareBook()
    {
        $user = $this->getUser();
        $emails = $this->getRequest()->get('emails');
        $message = $this->getRequest()->get('message');
        $isbn = $this->getRequest()->get('isbn');
        /** @var BookService $bookService */
        $bookService = $this->getSlx()->getBookService();
        /** Create adoption for each course review */
        $res = $bookService->shareBookWithUsers($user->getUserId(), $emails, $message, $isbn);

        return $this->renderJson(['success' => true]);
    }

    /**
     * delete recommended book from you like tab
     * @return \Slim\Http\Response
     * @throws Exception
     */
    public function deleteRecommendation()
    {
        $user = $this->getUser();
        $isbn = $this->getRequest()->get('isbn');
        /** @var BookService $bookService */
        $bookService = $this->getSlx()->getBookService();
        /** Create adoption for each course review */
        $bookService->deleteRecommendation($user->getUserId(),$isbn);

        return $this->renderJson(['success' => true]);
    }

    /**
     * delete book from shared book tab
     * @return \Slim\Http\Response
     * @throws Exception
     */
    public function deleteSharedRecommendation()
    {
        $user = $this->getUser();
        $isbn = $this->getRequest()->get('isbn');
        /** @var BookService $bookService */
        $bookService = $this->getSlx()->getBookService();
        /** Create adoption for each course review */
        $bookService->deleteSharedRecommendation($user->getUserId(), $isbn);

        return $this->renderJson(['success' => true]);
    }

    /**
     * @param $isbn
     * @param bool $student
     * @return \Slim\Http\Response
     * @throws NotFoundException
     * @throws Exception
     */
    public function showStudentTeacherResources($isbn,$student=false){
        /** @var BookService $bookService */
        $bookService = $this->getSlx()->getBookService();
        $ancillary = $this->fetchAncillaryContent($isbn);
        $book = $bookService->getBookDetailsByISBN($isbn);
        return $this->render('@MainBundle/book/students-teachers-resources.html.twig', [
            'ancillary' => $ancillary,
            'book'=>$book,
            'student'=> $student
        ]);
    }

    /**
     * Get reading list history
     */
    public function getListHistory() {

        $listId = $this->getRequest()->get('readingListId');
        $ListName = $this->getRequest()->get('ListName');
        $user = $this->getUser();
        /** Reading List Service */
        $readingListService = $this->getSlx()->getReadingListService();
        $readingListHistory = $readingListService->getReadingListHistory($user->getUserId(), $listId );
        return $this->render('@MainBundle/modal/history-modal.html.twig', [
            'readingListHistory' => $readingListHistory,
            'listName'=> $ListName,
            'listId' => $listId
        ]);
    }

    /**
     * Use this function to update session of search result after filter or update order
     */
    public function updateFilteredBooks() {

        if(file_get_contents('php://input')) {
            $books = json_decode(file_get_contents('php://input'));
            if($books && isset($books->booksToShow)) {
                $this->getApp()->session->set('booksNavigation', $books->booksToShow);
            }
        }
    }

    /**
     * @return void
     * @throws Exception
     * This function get isbn from ajax call and save it wen user click on read more button
     */
    public function saveUserEvent(): bool {
        $request = $this->getRequest();
        $isbn = $request->get('isbn');
        /** @var  $user */
        $user = $this->getUser();
        $result = false;
        if($user && $isbn) {
            /** @var BookService $bookService */
            $bookService = $this->getSlx()->getBookService();
            //call webservice to save user opened book event only for logged-in users
            $result = $bookService->saveUserEvent($isbn, $user->getUserId(), 'ReadNow');
        }
        return $result;
    }
}
