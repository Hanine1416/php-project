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

namespace MainBundle\Services;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use lib\Config;
use MainBundle\Entity\Book;
use MainBundle\Entity\BookInstitutionRequest;
use MainBundle\Entity\BookRequest;
use MBComponents\Exceptions\NotFoundException;
use MBComponents\Helpers\MainHelper;
use MBComponents\HttpFoundation\Session;
use MBComponents\Services\SlxWebService;
use stdClass;
use UserBundle\Entity\User;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

/**
 * Class BookService
 * @package MainBundle\Services
 */
class BookService extends SlxWebService
{

    /** @var string */
    private $closedWon = 'Closed - Won';

    /** @var string */
    private $closedLost = 'Closed - Lost';

    /**
     * Return featured book from slx WS category param is not required
     * @param $reg
     * @param $lang
     * @param $category
     * @return ArrayCollection
     */
    public function getFeatureBooks(string $reg, string $lang, string $category): ArrayCollection
    {
        $response = $this->getSoap(
            'elsGet_Featured',
            [
                'idsite' => Config::read('currentSiteId'),
                'region' => $reg,
                'language' => $lang,
                'topdiscipline' => $category
            ],
            false
        );
        $featuredBooks = new ArrayCollection();
        if ($response->elsGet_FeaturedResult->Result)
        {
            $foundBooks = $response->elsGet_FeaturedResult->ItemList->WSCatalogProduct;
            foreach ($foundBooks as $foundBook) {
                $book = new Book();
                foreach ($foundBook as $key => $val)
                {
                    $setter = 'set' . $key;
                    if (method_exists($book, $setter)) {
                        if ($setter == 'setPublicationDate') {
                            $val = [$lang => $val];
                        }
                        $book->$setter($val);
                    }
                }
                $featuredBooks->add($book);
            }
        }
        return $featuredBooks;
    }

    /**
     * Return recommended book for a user from slx WS
     * @param $reg
     * @param $lang
     * @param $userId
     * @param $start
     * @param $numItems
     * @return ArrayCollection
     * @throws NotFoundException
     */
    public function getRecommendedBooks(string $reg, string $lang, string $userId,int $start= null,int $numItems = null ): array
    {
        $response = $this->getSoap(
            'elsGet_UserRecommendations',
            ['idsite' => Config::read('currentSiteId'), 'region' => $reg, 'language' => $lang, 'userid' => $userId,'start' =>$start,'numitems'=>$numItems]
        );
        $recommendedBooks = new ArrayCollection();
        if (!$response->elsGet_UserRecommendationsResult->Result) {
            return [$recommendedBooks, 0];
        }

        $foundBooks = [];
        if(isset($response->elsGet_UserRecommendationsResult->ItemList) &&
            isset($response->elsGet_UserRecommendationsResult->ItemList->WSCatalogProduct)) {
            $foundBooks = $response->elsGet_UserRecommendationsResult->ItemList->WSCatalogProduct;
        }

        $arrayRecommendations = [];
        if(!is_array($foundBooks)) {
            array_push($arrayRecommendations,$foundBooks);
        }
        else {
            $arrayRecommendations = $foundBooks;
        }

        foreach ($arrayRecommendations as $recommendations)
        {
            $book = new Book();
            if ($recommendations) {
                if (isset($recommendations->AvailableTypes)) {
                    if (is_array($recommendations->AvailableTypes->string)) {
                        foreach ($recommendations->AvailableTypes->string as $val) {
                            $book->addAvailableType($val);
                        }
                    } else {
                        $book->addAvailableType($recommendations->AvailableTypes->string);
                    }
                }
                unset($recommendations->AvailableTypes);
                $book = $this->addBooksSetter($book,$recommendations,$lang);
                $book->setTag($this->getBookTag($recommendations->IsNew, $recommendations->IsMostPopular, $recommendations->IsTopSeller, $recommendations->HasUpdatedEdition));

                $recommendedBooks->add($book);
            }
        }
        return [$recommendedBooks, $response->elsGet_UserRecommendationsResult->NumberOfRecords];
    }

    /**
     * this function add setter to the given book
     * @param $book
     * @param $foundBooks
     * @param string $lang
     * @return mixed
     */
    public function addBooksSetter($book, $foundBooks, string $lang) {

        foreach ($foundBooks as  $key => $val)
        {
            $setter = 'set' . $key;
            if (method_exists($book, $setter))
            {
                $setter == 'setPublicationDate' ? $book->$setter([$lang => $val]) : $book->$setter($val);
            }
        }
        return $book;

    }

    /**
     * getCoursesList
     * @param $reg
     * @param  $lang
     * @return ArrayCollection
     */

    public function getCoursesList(string $reg, string $lang): ArrayCollection
    {
        $params = [
            'idsite' => Config::read('currentSiteId'),
            'region' => $reg,
            'language' => $lang,
            'picklistName' => 'StandardSubjectList'
        ];
        $response = $this->getSoap('elsGet_PickList', $params, true);
        $coursesList = new ArrayCollection();
        if ($response->elsGet_PickListResult->Result &&
            isset($response->elsGet_PickListResult->ItemList->PickListItem)) {
            $foundCourse = $response->elsGet_PickListResult->ItemList->PickListItem;
            foreach ($foundCourse as $course)
            {
                $courses = [];
                $courses['id'] = $course->Id;
                $courses['text'] = $course->Text;
                $courses['shortText'] = $course->Shorttext;
                $courses['picklistId'] = $course->Picklistid;
                $courses['itemId'] = $course->Itemid;
                $coursesList->add($courses);
            }
        }
        return $coursesList;
    }

    /**
     * getCourseLevels
     * @param $reg
     * @param  $lang
     * @return ArrayCollection
     */

    public function getCourseLevels(string $reg, string $lang): ArrayCollection
    {
        $params = [
            'idsite' => Config::read('currentSiteId'),
            'region' => $reg,
            'language' => $lang,
            'picklistName' => 'Course List'
        ];
        $response = $this->getSoap('elsGet_PickList', $params, true);
        $levelsList = new ArrayCollection();
        if ($response->elsGet_PickListResult->Result &&
            isset($response->elsGet_PickListResult->ItemList->PickListItem))
        {
            $foundLevel = $response->elsGet_PickListResult->ItemList->PickListItem;
            foreach ($foundLevel as $level)
            {
                $levels = [];
                $levels['id'] = $level->Id;
                $levels['text'] = $level->Text;
                $levels['picklistId'] = $level->Picklistid;
                $levels['itemId'] = $level->Itemid;
                $levelsList->add($levels);
            }
        }
        return $levelsList;
    }

    /**
     * return book details by isbn
     * @param string $isbn
     * @return Book
     * @throws NotFoundException
     */
    public function getBookDetailsByISBN(string $isbn): ?Book
    {
        $response = $this->getSoap(
            'elsGet_CatalogItem',
            ['idsite' => Config::read('currentSiteId'), 'isbn' => $isbn],
            false
        );
        $book = null;
        if (!isset($response->elsGet_CatalogItemResult->ErrorCode) && $response->elsGet_CatalogItemResult->PpmXML)
        {
            $book = new Book(simplexml_load_string($response->elsGet_CatalogItemResult->PpmXML));
            $book->setExternalBookId($response->elsGet_CatalogItemResult->ExternalBookId);
            $book->setExternalBookIdPrev($response->elsGet_CatalogItemResult->ExternalBookIdPrev);
            $book->setRating(floatval($response->elsGet_CatalogItemResult->Rating));
            $book->setNumReviews(floatval($response->elsGet_CatalogItemResult->NumReviews));
            $items = (array)$response->elsGet_CatalogItemResult;
            $availableType = null;
            $book->setCkAvailable($response->elsGet_CatalogItemResult->CKAvailable);
            $book->setCkUrl($response->elsGet_CatalogItemResult->CKUrl);
            if (array_key_exists('AvailableTypes', $items))
            {
                $availableType = $items['AvailableTypes'];
            }

            if (isset($response->elsGet_CatalogItemResult->Ancillary))
            {
                foreach ($response->elsGet_CatalogItemResult->Ancillary as $val)
                {
                    if (is_array($response->elsGet_CatalogItemResult->Ancillary->ProductAncillary))
                    {
                        $book->addAncillary($val);
                    } else
                        {
                        $book->addAncillary([$val]);
                    }
                }
            }

            if (isset($availableType)) {
                if (is_array($availableType->string)) {
                    foreach ($availableType->string as $val) {
                        $book->addAvailableType($val);
                    }
                } else {
                    $book->addAvailableType($availableType->string);
                }
            }
            unset($availableType);
            //Set book tags
            $bookData = $response->elsGet_CatalogItemResult;
            $book->setTag($this->getBookTag($bookData->IsNew, $bookData->IsMostPopular, $bookData->IsTopSeller, $bookData->HasUpdatedEdition));
        } else {
            throw new NotFoundException('Sorry! This title (ISBN ' . $isbn . ')  is not available on inspectioncopy.elsevier.com');
        }

        return $book;
    }

    /**
     * return bool if books exists by isbn
     * @param string $isbn
     * @return bool
     */
    public function getBookExistsISBN(string $isbn): bool
    {
        $response = $this->getSoap(
            'elsGet_CatalogItem',
            ['idsite' => Config::read('currentSiteId'), 'isbn' => $isbn],
            true
        );
        $bookExist = true;
        if (isset($response->elsGet_CatalogItemResult->ErrorCode)) {
            return false;
        }
        return $bookExist;
    }


    /**
     * Return list of currency for specific region
     * @param $region
     * @return array
     */
    public function getCurrencyRegion(string $region): array
    {
        $response = $this->getSoap(
            'elsGet_PickList',
            [
                'idsite' => Config::read('currentSiteId'),
                'picklistName' => 'RegionICCurrency',
                'language' => '',
                'region' => $region
            ],
            false
        );
        $output = array();
        if (!isset($response->elsGet_PickListResult->ErrorCode) &&
            is_array($response->elsGet_PickListResult->ItemList->PickListItem)) {
            /** sorting list of currency  */
            foreach ($response->elsGet_PickListResult->ItemList->PickListItem as $val) {
                if (!is_array($response->elsGet_PickListResult->ItemList->PickListItem)) {
                    $val = $response->elsGet_PickListResult->ItemList->PickListItem;
                }
                $output[$val->Shorttext][] = $val->Text;
            }
            ksort($output);
        }
        return $output;
    }

    /**
     * get all user's books ( include requested & cancelled & pending )
     * @param null $userId
     * @param bool $withAdoption
     * @return array|null
     */
    public function getUserBooks($userId, $withAdoption = true): array
    {
        $response = $this->getSoap(
            'elsGet_UserBooksExtended',
            ['idsite' => Config::read('currentSiteId'), 'userid' => $userId]
        );
        $userBooks = array(
            'Pending' => [],
            'Approved' => [],
            'NotReviewed' => [],
            'Adopted' => [],
            'NotAdopted' => [],
            'MixedAdoption' => [],
            'Renewal' => [],
            'Renewed' => [],
            'NotRenewed' => [],
            'MixedRenewal' => [],
            'Expired' => [],
            'Declined' => [],
            'Cancelled' => []
        );
        $listIsbn = array();
        if ($response->elsGet_UserBooksExtendedResult->Result) {
            $userBookList = $response->elsGet_UserBooksExtendedResult->List;
            if (!is_array($userBookList->ContactBook)) {
                $contactBooks[] = $userBookList->ContactBook;
            } else {
                $contactBooks = $userBookList->ContactBook;
            }
            foreach ($contactBooks as $contactBook) {
                $copies = [];
                if (isset($contactBook->Copies->ActiveBook)) {
                    if (!is_array($contactBook->Copies->ActiveBook)) {
                        $copies[] = $contactBook->Copies->ActiveBook;
                    } else {
                        $copies = $contactBook->Copies->ActiveBook;
                    }
                }
                foreach ($copies as $copy) {

                    $endDate = isset($copy->Enddate) ? explode(' ', $copy->Enddate) : '';
                    $enabledDate = isset($copy->Enableddate) ? explode(' ', $copy->Enableddate) : '';
                    $userBook = array(
                        'Isbn' => $copy->Isbn,
                        'Status' => $copy->Status,
                        'Format' => $copy->Format,
                        'PreOrder' => $copy->Preorder,
                        'EndDate' => $copy->Format === 'Digital' && $endDate[0] !== '' ?
                            DateTime::CreateFromFormat('m/d/Y', $endDate[0])->format('d-m-Y') : '',
                        'EnabledDate' => $enabledDate[0],
                        'SiteId' => $copy->Siteid,
                        'Site' => $copy->Site,
                        'ProductId' => $copy->Productid,
                        'ExternalBookId' => $copy->Format === 'Digital' ? $copy->Externalbookid : '',
                        'SentDate' => $copy->Format === 'Print' ? $copy->SentDate : '',
                        'RequestedDate'=> $copy->Requesteddate,
                        'Institutions' => (isset($copy->InstitutionList) ?
                            json_decode(json_encode($copy->InstitutionList), true) : [])
                    );
                    try {
                        /** get book details */
                        $bookDetails = $this->getBookDetailsByISBN($copy->Isbn);
                        $userBook['Details'] = $bookDetails;
                    } catch (NotFoundException $e) {
                        $userBook['Details'] = null;
                    }
                    if (!isset($userBooks[$copy->Status])) {
                        $userBooks[$copy->Status] = [];
                    }
                    if ($copy->Status == 'Pending' || $copy->Status == 'Approved' || $copy->Status == 'Expired') {
                        if (isset($userBooks[$copy->Status][$copy->Isbn]) && $copy->Format == 'Digital') {
                            $userBooks[$copy->Status][$copy->Isbn]['EndDate'] = $userBook['EndDate'];
                            $userBooks[$copy->Status][$copy->Isbn]['ExternalBookId'] = $userBook['ExternalBookId'];
                        } elseif (isset($userBooks[$copy->Status][$copy->Isbn]) && $copy->Format == 'Print') {
                            $userBooks[$copy->Status][$copy->Isbn]['SentDate'] = $userBook['SentDate'];
                        } else {
                            $userBooks[$copy->Status][$copy->Isbn] = $userBook;
                        }
                    } else {
                        array_push($userBooks[$copy->Status], $userBook);
                    }
                }
                /** Add reviewed books */
                if ($withAdoption && isset($contactBook->Adoptions->Adoption)) {
                    $adoptions = is_array($contactBook->Adoptions->Adoption) ?
                        $contactBook->Adoptions->Adoption : [$contactBook->Adoptions->Adoption];
                    /** If book adoption not done by a request then add book information */
                    if (!isset($userBooks['Approved'][$contactBook->Isbn]) &&
                        !isset($userBooks['Expired'][$contactBook->Isbn])) {
                        $book['Isbn'] = $contactBook->Isbn;
                        try {
                            /** Get book details */
                            $book['Details'] = $this->getBookDetailsByISBN($contactBook->Isbn);
                        } catch (NotFoundException $e) {
                            $book['Details'] = null;
                        }
                    } else {
                        $book = isset($userBooks['Approved'][$contactBook->Isbn]) ?
                            $userBooks['Approved'][$contactBook->Isbn] : $userBooks['Expired'][$contactBook->Isbn];
                        /** Clear unnecessary data */
                        unset($book['Site'], $book['SiteId'], $book['Institutions']);
                    }
                    $book['Feedback'] = $this->getBookFeedBack($userId, $contactBook->Productid);
                    $book['adoptions'] = [];
                    foreach ($adoptions as $adoption) {
                        $adoption->ProductId = $contactBook->Productid;
                        $adoption->Type == 'New' ? $this->addAdoption($userBooks, $adoption, $contactBook, $book) :
                            $this->addRenewal($userBooks, $adoption, $contactBook, $book);
                    }
                }
            }
        }
        return $userBooks;
    }

    public function addAdoption(&$userBooks, $adoption, $contactBook, $book)
    {
        if ($adoption->Status === $this->closedWon)
        {
            /** If book has also a not confirmed adoption then move to MixedAdoption */
            if (isset($userBooks['NotAdopted'][$contactBook->Isbn]) ||
                isset($userBooks['MixedAdoption'][$contactBook->Isbn])) {
                if (!isset($userBooks['MixedAdoption'][$contactBook->Isbn])) {
                    $userBooks['MixedAdoption'][$contactBook->Isbn] = $book;
                    $userBooks['MixedAdoption'][$contactBook->Isbn]['Adopted'] = [];
                    $userBooks['MixedAdoption'][$contactBook->Isbn]['NotAdopted'] =
                        $userBooks['NotAdopted'][$contactBook->Isbn]['adoptions'];
                    unset($userBooks['NotAdopted'][$contactBook->Isbn]);
                }
                array_push(
                    $userBooks['MixedAdoption'][$contactBook->Isbn]['Adopted'],
                    $adoption
                );
            } else {
                if (!isset($userBooks['Adopted'][$contactBook->Isbn])) {
                    $userBooks['Adopted'][$contactBook->Isbn] = $book;
                }
                array_push($userBooks['Adopted'][$contactBook->Isbn]['adoptions'], $adoption);
            }
        } elseif ($adoption->Status === $this->closedLost)
        {
            /** If book has also a confirmed adoption then move to MixedAdoption */
            if (isset($userBooks['Adopted'][$contactBook->Isbn]) ||
                isset($userBooks['MixedAdoption'][$contactBook->Isbn])) {
                if (!isset($userBooks['MixedAdoption'][$contactBook->Isbn])) {
                    $userBooks['MixedAdoption'][$contactBook->Isbn] = $book;
                    $userBooks['MixedAdoption'][$contactBook->Isbn]['NotAdopted'] = [];
                    $userBooks['MixedAdoption'][$contactBook->Isbn]['Adopted'] =
                        $userBooks['Adopted'][$contactBook->Isbn]['adoptions'];
                    unset($userBooks['Adopted'][$contactBook->Isbn]);
                }
                array_push(
                    $userBooks['MixedAdoption'][$contactBook->Isbn]['NotAdopted'],
                    $adoption
                );
            } else {
                if (!isset($userBooks['NotAdopted'][$contactBook->Isbn])) {
                    $userBooks['NotAdopted'][$contactBook->Isbn] = $book;
                }
                array_push(
                    $userBooks['NotAdopted'][$contactBook->Isbn]['adoptions'],
                    $adoption
                );
            }
        } else {
            if (!isset($userBooks['NotReviewed'][$contactBook->Isbn])) {
                $userBooks['NotReviewed'][$contactBook->Isbn] = $book;
            }
            array_push($userBooks['NotReviewed'][$contactBook->Isbn]['adoptions'], $adoption);
        }
    }

    public function addRenewal(&$userBooks, $adoption, $contactBook, $book)
    {
        if ($adoption->Status == $this->closedWon)
        {
            /** If book has also a not confirmed adoption then move to MixedAdoption */
            if (isset($userBooks['NotRenewed'][$contactBook->Isbn]) ||
                isset($userBooks['MixedRenewal'][$contactBook->Isbn]))
            {
                if (!isset($userBooks['MixedRenewal'][$contactBook->Isbn]))
                {
                    $userBooks['MixedRenewal'][$contactBook->Isbn] = $book;
                    $userBooks['MixedRenewal'][$contactBook->Isbn]['Renewed'] = [];
                    $userBooks['MixedRenewal'][$contactBook->Isbn]['NotRenewed'] =
                        $userBooks['NotRenewed'][$contactBook->Isbn]['adoptions'];
                    unset($userBooks['NotRenewed'][$contactBook->Isbn]);
                }
                /** Keep only previous year adoption history */
                if (count($userBooks['MixedRenewal'][$contactBook->Isbn]['Renewed']) > 0)
                {
                    $adp = $userBooks['MixedRenewal'][$contactBook->Isbn]['Renewed'][0];
                    if (substr($adoption->EstimatedClose, 0, 4) >
                        substr($adp->EstimatedClose, 0, 4))
                    {
                        {
                            $userBooks['MixedRenewal'][$contactBook->Isbn]['Renewed'] = [];
                        }
                    }
                }
                array_push(
                    $userBooks['MixedRenewal'][$contactBook->Isbn]['Renewed'],
                    $adoption
                );
            }
            if (!isset($userBooks['Renewed'][$contactBook->Isbn])) {
                $userBooks['Renewed'][$contactBook->Isbn] = $book;
            }
            /** Keep only previous year adoption history */
            if (count($userBooks['Renewed'][$contactBook->Isbn]['adoptions']) > 0)
            {
                $adp = $userBooks['Renewed'][$contactBook->Isbn]['adoptions'][0];
                if (substr($adoption->EstimatedClose, 0, 4) >
                    substr($adp->EstimatedClose, 0, 4))
                {
                    $userBooks['Renewed'][$contactBook->Isbn]['adoptions'] = [];
                }
            }
            array_push($userBooks['Renewed'][$contactBook->Isbn]['adoptions'], $adoption);
        } elseif ($adoption->Status == $this->closedLost)
        {
            /** If book has also a confirmed adoption then move to MixedAdoption */
            if (isset($userBooks['Renewed'][$contactBook->Isbn]) ||
                isset($userBooks['MixedRenewal'][$contactBook->Isbn]))
            {
                if (!isset($userBooks['MixedRenewal'][$contactBook->Isbn]))
                {
                    $userBooks['MixedRenewal'][$contactBook->Isbn] = $book;
                    $userBooks['MixedRenewal'][$contactBook->Isbn]['NotRenewed'] = [];
                    $userBooks['MixedRenewal'][$contactBook->Isbn]['Renewed'] =
                        $userBooks['Renewed'][$contactBook->Isbn]['adoptions'];
                    unset($userBooks['Renewed'][$contactBook->Isbn]);
                }
                /** Keep only previous year adoption history */
                if (count($userBooks['MixedRenewal'][$contactBook->Isbn]['NotRenewed']) > 0)
                {
                    $adp = $userBooks['MixedRenewal'][$contactBook->Isbn]['NotRenewed'][0];
                    if (substr($adoption->EstimatedClose, 0, 4) >
                        substr($adp->EstimatedClose, 0, 4))
                    {
                        $userBooks['MixedRenewal'][$contactBook->Isbn]['NotRenewed'] = [];
                    }
                }
                array_push(
                    $userBooks['MixedRenewal'][$contactBook->Isbn]['NotRenewed'],
                    $adoption
                );
            }
            if (!isset($userBooks['NotRenewed'][$contactBook->Isbn]))
            {
                $userBooks['NotRenewed'][$contactBook->Isbn] = $book;
            }
            /** Keep only previous year adoption history */
            if (count($userBooks['NotRenewed'][$contactBook->Isbn]['adoptions']) > 0)
            {
                $adp = $userBooks['NotRenewed'][$contactBook->Isbn]['adoptions'][0];
                if (substr($adoption->EstimatedClose, 0, 4) >
                    substr($adp->EstimatedClose, 0, 4))
                {
                    $userBooks['NotRenewed'][$contactBook->Isbn]['adoptions'] = [];
                }
            }
            array_push($userBooks['NotRenewed'][$contactBook->Isbn]['adoptions'], $adoption);
        } else {
            if (!isset($userBooks['Renewal'][$contactBook->Isbn]))
            {
                $userBooks['Renewal'][$contactBook->Isbn] = $book;
            }
            array_push($userBooks['Renewal'][$contactBook->Isbn]['adoptions'], $adoption);
        }
    }

    /**
     * Do a book request from slx
     * @param BookRequest $bookRequest
     * @param User $user
     * @param string $operationType
     * @return array
     */
    public function requestBook(BookRequest $bookRequest, User $user, $operationType = 'Add'): array
    {
        $institutionsList = [];
        /** @var BookInstitutionRequest $institution */
        foreach ($bookRequest->getInstitutions() as $institution)
        {
            $instData = [
                'InstitutionID' => $institution->getInstitutionId(),
                'Course' => ($institution->getCourse() != "false") ? $institution->getCourse() : '',
                'InstitutionCourseName' => $institution->getCourseName(),
                'Students' => $institution->getStudentsNumber(),
                'StartDate' => $institution->getStartDate(),
                'EndDate' => $institution->getEndDate(),
                'BookUsedReason' => $institution->getBookUsedReason(),
                'CurrentBook' => $institution->getCurrentUsedBook(),
                'Coursecode' => $institution->getCourseCode(),
                'Courselevel' => $institution->getCourseLevel()
            ];
            if ($institution->getRecLevel()) {
                $instData['RecLevel'] = $institution->getRecLevel();
            }
            array_push($institutionsList, $instData);
        }
        /** Init Ws call parameters */
        $params = [
            'idsite' => Config::read('currentSiteId'),
            'userid' => $user->getUserId(),
            'isbn' => $bookRequest->getBookIsbn(),
            'type' => $operationType,
            'format' => $bookRequest->getBookFormat(),
            'preorder' => $bookRequest->isPreOrder(),
            'addressid' => $bookRequest->getAddressId(),
            'institutionlist' => $institutionsList
        ];
        /** Send request to slx */
        $response = $this->getSoap('elsSet_UserBook', $params, false);
        return (array)$response->elsSet_UserBookResult;
    }

    /**
     * Return books of single or multiples subcategories if the isbn is provided then don't include the book to the list
     * @param ArrayCollection|array $subCategories
     * @param string $region
     * @param string $language
     * @param bool $order
     * @param $isbn
     * @return ArrayCollection
     */
    public function getBooksBySubCategories(
        ArrayCollection $subCategories,
        string $region = '7',
        string $language = 'en',
        bool $order = false,
        string $isbn = null
    ): ArrayCollection
    {
        $books = new ArrayCollection();
        foreach ($subCategories as $subCategory)
        {
            /** First we should search for books from each category name from the slx WS*/
            $result = $this->getSoap(
                'elsGet_CatalogEx',
                [
                    'idsite' => Config::read('currentSiteId'),
                    'region' => $region,
                    'language' => $language,
                    'discipline' => $subCategory['name'],
                    'topdiscipline' => ''
                ],
                true
            );
            if (!$result->elsGet_CatalogExResult->Result)
            {
                return $books;
            }
            /** Retrieve all the found book and cast them to Book object */
            /** Check if the ws returned only book */
            if (!is_array($result->elsGet_CatalogExResult->ItemList->WSCatalogProduct))
            {
                $rawBooks = [$result->elsGet_CatalogExResult->ItemList->WSCatalogProduct];
            } else {
                $rawBooks = $result->elsGet_CatalogExResult->ItemList->WSCatalogProduct;
            }
            /** The ws returned many books  */
            foreach ($rawBooks as $rawBook)
            {
                /** Check if the book should be skipped or not by the provided isbn */
                if ($isbn !== $rawBook->Isbn)
                {
                    $book = new Book();
                    $book->setIsbn($rawBook->Isbn);
                    $book->setTitle($rawBook->Title);
                    $book->setAuthor($rawBook->Author);
                    $book->setDescription($rawBook->Description);
                    $book->setLanguage($rawBook->Language);
                    $book->setTag($this->getBookTag($rawBook->IsNew, $rawBook->IsMostPopular, $rawBook->IsTopSeller, $rawBook->HasUpdatedEdition));
                    if (isset($rawBook->PublicationDate))
                    {
                        $book->setPublicationDate([
                            $language => date('M o', strtotime($rawBook->PublicationDate))
                        ]);
                    }
                    $book->setRegion($rawBook->Region);
                    $book->setDisciplines($rawBook->Disciplines);
                    $books->add($book);
                }
            }
        }
        /** Remove all duplicated book that belong to the same subcategories */
        $result = array_unique($books->toArray());
        /** If order parameter is true then order the books by production date */
        if ($order) {
            usort($result, function (Book $firstBook, Book $secondBook) use ($language)
            {
                $sortReturn = null;
                $a = isset($firstBook->getPublicationDate()[$language]);
                $b = isset($secondBook->getPublicationDate()[$language]);
                /** First check if one of the book doesn't have a publication date */
                if ($a && !$b)
                {
                    $sortReturn = -1;
                } elseif (!$a &&
                    $b) {
                    $sortReturn = 1;
                } elseif (!$a &&
                    !$b) {
                    $sortReturn = 0;
                } else {
                    /** Now if the 2 books has a publication date then compare by the date */
                    $firstBookDate = DateTime::createFromFormat('M Y', $firstBook->getPublicationDate()[$language]);
                    $secondBookDate = DateTime::createFromFormat('M Y', $secondBook->getPublicationDate()[$language]);
                    $sortReturn = $firstBookDate->getTimestamp() > $secondBookDate->getTimestamp() ? -1 : 1;
                }
                return $sortReturn;
            });
        }
        return new ArrayCollection($result);
    }

    /**
     * Return all available categories along with their subcategories in slx
     * @return array
     */
    public function getCategories(): array
    {
        /** Read the language & region code from session to pick the right categories */
        $region = $this->container->get(Session::class)->get('region');
        $lang = $this->container->get(Session::class)->get('lang');
        $response = $this->getSoap(
            'elsGet_PickList',
            [
                'idsite' => Config::read('currentSiteId'),
                'picklistName' => 'Disciplines',
                'region' => $region,
                'language' => $lang
            ],
            true
        );
        $result = array();
        /** Check if the WS returned data & the data is not empty  */
        if ($response->elsGet_PickListResult->Result &&
            is_array($response->elsGet_PickListResult->ItemList->PickListItem))
        {
            foreach ($response->elsGet_PickListResult->ItemList->PickListItem as $key => $val)
            {
                /** Check if the data contain only one category */
                if (!is_array($response->elsGet_PickListResult->ItemList->PickListItem))
                {
                    $val = $response->elsGet_PickListResult->ItemList->PickListItem;
                }
                $other = '';
                /** We use urlencode() function to encode cat link slash with underscore  */
                if ($lang == 'br') {
                    /** Test if the item is a category */
                    if (strlen($val->Shorttext) < 18) {
                        $discipline = mb_strtolower($val->Text, 'UTF-8');
                        $result[$val->Shorttext]['category'] = ucfirst($discipline);
                        /** Test if the item is a "other subcategory" */
                    } elseif (substr($val->Shorttext, 0, 17) == $other)
                    {
                        $subCategory = [
                            'text' => $val->Text,
                            'Picklistid' => $val->Picklistid,
                            'region' => $region,
                            'language' => $lang,
                            'textEncode' => urlencode(preg_replace('/\//', '_', $val->Text))];
                        $result[substr($val->Shorttext, 0, 17)]['subcategories'][] = $subCategory;
                    } else {
                        /** Case the item is a subcategory */
                        $subCategory = [
                            'text' => $val->Text,
                            'Picklistid' => $val->Picklistid,
                            'region' => $region,
                            'language' => $lang,
                            'textEncode' => urlencode(preg_replace('/\//', '_', $val->Text))];
                        $result[substr($val->Shorttext, 0, 16)]['subcategories'][] = $subCategory;
                    }
                }
                elseif (strlen($val->Shorttext) == 9)
                {
                    $result[$val->Shorttext]['category'] = $val->Text;
                } else {
                    $subCategory = [
                        'text' => $val->Text,
                        'Picklistid' => $val->Picklistid,
                        'region' => $region,
                        'language' => $lang,
                        'textEncode' => urlencode(preg_replace('/\//', '_', $val->Text))];
                    $result[substr($val->Shorttext, 0, 9)]['subcategories'][] = $subCategory;
                }
            }
            /** After putting each subcategory under it's parent we should order them alphabetically */
            foreach ($result as $key => $category)
            {
                if (isset($result[$key]['subcategories']))
                {
                    usort($result[$key]['subcategories'], function ($subCat1, $subCat2) {
                        return strcmp($subCat1['text'], $subCat2['text']);
                    });
                }
            }
            /** Apply a specific order in the spanish language (IC-569)*/
            if ($lang == 'es') {
                /** Force the indexes (ShortText) in the requested order */
                $result = array_merge(array(
                    'PROMISH03' => null,
                    'PROMISH04' => null,
                    'PROMISH01' => null,
                    'PROMISH05' => null,
                    'PROMISH02' => null,
                ), $result);
            }
        }
        return $result;
    }

    /**
     * Search book by category or title or author or isbn or description
     * with applying pagination & sorting then as user wish
     * @param array $searchedCategories
     * @param string $sort
     * @param string $search
     * @param string $searchBy
     * @param  $userId
     * @return array
     */
    public function searchBooks(array $searchedCategories, string $sort,
                                string $search, string $searchBy, $userId, $userLang = null, $userRegion = null): array
    {
        /** Get language and region  */
        if ($userLang && $userRegion) {
            $lang   = $userLang;
            $region = $userRegion;
        } else {
            $lang = $this->container->get(Session::class)->get('lang');
            $region = $this->container->get(Session::class)->get('region');
        }
        //rest book navigation session
        $this->container->get(Session::class)->set('booksNavigation', '');
        /** Init all params needed for calling the slx WS */
        $params = [
            'idsite' => Config::read('currentSiteId'),
            'region' => $region,
            'language' => strtoupper($lang),
            'discipline' => '',
            'topdiscipline' => $searchedCategories,
        ];
        /** Check if the user is searching for a book by author or isbn or title or description */
        if (!empty($search) && $searchBy && in_array($searchBy, ['author', 'isbn', 'title', 'description']))
        {
            $params[$searchBy] = $searchBy === 'isbn' ?
                trim(str_replace('-', '', $search)) : trim($search);
        }
        $params['topdiscipline'] = array_slice($searchedCategories, 0, 3);
        $response = $this->getSoap('elsGet_CatalogEx', $params, true);
        $products = [];
        if ($response->elsGet_CatalogExResult->Result &&
            isset($response->elsGet_CatalogExResult->ItemList->WSCatalogProduct))
        {
            $products = $response->elsGet_CatalogExResult->ItemList->WSCatalogProduct;
            /** If ws returned only one product then transform products to array instead of object */
            if (is_object($products)) {
                $products = [$products];
            }
        }
        /** if searching for more then 3 categories then divide the call to avoid soap timeout error*/
        if (count($searchedCategories) > 3)
        {
            $params['topdiscipline'] = array_slice($searchedCategories, 3);
            $response = $this->getSoap('elsGet_CatalogEx', $params, true);
            if ($response->elsGet_CatalogExResult->Result &&
                isset($response->elsGet_CatalogExResult->ItemList->WSCatalogProduct))
            {
                $response = $response->elsGet_CatalogExResult->ItemList->WSCatalogProduct;
                /** If ws returned only one product then transform products to array instead of object */
                if (is_object($response)) {
                    $response = [$response];
                }
                $products = array_merge($products, $response);
            }
        }
        /** Get user books */
        isset($userId) ? $userBooks = $this->getAllUserBooks($userId) : $userBooks = [];
        $books = [];
        $filters = [
            'userCopies' => [],
            'categories' => [],
            'years' => [],
            'tag' => []
        ];

        $categories = $this->getCategories();

        /** when user is connected add filter to get requested book and the books on my reading list */
        if ($userId) {
            $filters['userCopies']['requested'] = 0;
            $filters['userCopies']['readingList'] = 0;
        }

        /** add filter to get number of digital copies */
        $filters['userCopies']['hasDigital'] = 0;

        /** add filter to get number of Books whith tag "new" */
        $filters['userCopies']["isNew"] = 0;

        /** add filter to get number of Books whith tag "top seller" */
        $filters['userCopies']['isTopSeller'] = 0;

        /** add filter to get number of Books whith tag "most popular" */
        $filters['userCopies']['isMostPopular'] = 0;

        /** add filter to get number of published books */
        $filters['years']['published'] = 0;

        /** add filter to get number of books camming soon*/
        $filters['years']['soon'] = 0;

        /** add filter to get Student Ressource */
        $filters['userCopies']['HasStudentResources'] = 0;

        /** add filter to get Professor Ressource */
        $filters['userCopies']['HasProfessorResources'] = 0;

        /** loop through each product to get book details and filters */
        foreach ($products as $product)
        {
            if (!array_key_exists($product->Isbn, $books))
            {
                $allProducts = $this->getBookSubCategories($product);
                usort($allProducts, static function ($a, $b)
                {
                    return strcmp($a['name'], $b['name']);
                });

                if ($product->Isbn !== '')
                {
                    $book = [
                        'author' => $product->Author,
                        'title' => $product->Title,
                        'isbn' => $product->Isbn,
                        'subcategories' => $allProducts,
                        'date' => $product->PUBDATEOPCO ?? '',
                        'requests' => $product->IcRequests,
                        'digital' => $product->Hasdigital,
                        'tag' => $this->getBookTag($product->IsNew, $product->IsMostPopular, $product->IsTopSeller, $product->HasUpdatedEdition),
                        'numReviews' =>$product->NumReviews,
                        'rating' => $product->Rating,
                        'StudentRessource' => $product->HasStudentResources,
                        'InstructorRessource' => $product->HasProfessorResources
                    ];

                    $this->getFiltersFromBook($book, $filters, $categories, $searchedCategories, $userBooks);
                    $books[$product->Isbn] = $book;
                }
            }
        }

        [$orderBy, $order] = explode('-', $sort);
        $books = $this->sortBooks($books, $orderBy, $order);

        /** Sort Filters */
        ksort($filters['years']);
        if (isset($filters['categories']['SH']))
        {
            ksort($filters['categories']['SH']);
            foreach ($filters['categories']['SH'] as $key => $subcategory)
            {
                ksort($subcategory['subcategories']);
                $filters['categories']['SH'][$key]['subcategories'] = $subcategory['subcategories'];
            }
        }
        if (isset($filters['categories']['ST']))
        {
            ksort($filters['categories']['ST']);
            foreach ($filters['categories']['ST'] as $key => $subcategory)
            {
                ksort($subcategory['subcategories']);
                $filters['categories']['ST'][$key]['subcategories'] = $subcategory['subcategories'];
            }
        }
        return [$books, $filters, $userBooks];
    }

    /**
     * Return filters that can be applicable for list of books  (categories & subcategories & year)
     * @param $book
     * @param $filters
     * @param $categories
     * @param $searchedCategories
     * @param $userBooks
     */
    public function getFiltersFromBook($book, &$filters, $categories, $searchedCategories, $userBooks): void
    {
        /** Get subcategories from the book */
        $bookCounted = ['ST' => false, 'SH' => false];
        foreach ($book['subcategories'] as $bookSubcategory)
        {
            $bookSubcategoryName = trim($bookSubcategory['name']);
            $categoryType = strpos($bookSubcategory['parent'], 'PROMISH') !== false ? 'SH' : 'ST';

            if (array_key_exists($bookSubcategory['parent'], $categories))
            {
                if(isset($categories[$bookSubcategory['parent']]['category'])) {
                    $categoryName = $categories[$bookSubcategory['parent']]['category'];
                    if (empty($searchedCategories) || in_array($categoryName, $searchedCategories, true)) {
                        if (!isset($filters['categories'][$categoryType])) {
                            $filters['categories'][$categoryType] = [];
                            $filters['categories'][$categoryType][$categoryName] = ['subcategories' => [$bookSubcategoryName => 1], 'total' => 1];
                            $bookCounted[$categoryType] = true;
                        } elseif (!array_key_exists($categoryName, $filters['categories'][$categoryType])) {
                            $filters['categories'][$categoryType][$categoryName] = ['subcategories' => [$bookSubcategoryName => 1], 'total' => 1];
                            $bookCounted[$categoryType] = true;
                        } elseif (!array_key_exists($bookSubcategoryName, $filters['categories'][$categoryType][$categoryName]['subcategories'])) {
                            $filters['categories'][$categoryType][$categoryName]['subcategories'][$bookSubcategoryName] = 1;
                        } else {
                            $filters['categories'][$categoryType][$categoryName]['subcategories'][$bookSubcategoryName]++;
                        }
                        if (!$bookCounted[$categoryType]) {
                            $filters['categories'][$categoryType][$categoryName]['total']++;
                            $bookCounted[$categoryType] = true;
                        }
                    }
                }
            }
        }

        /** ToDo dump the result of filters for requested and adopted and approved */
        if (isset($userBooks))
        {
            foreach ($userBooks as $userBook => $userBookIsbn)
            {
                if ($userBook == "Approved" || $userBook == "Adopted")
                {
                    if (in_array($book['isbn'], $userBooks[$userBook]))
                    {
                        $filters['userCopies']['requested']++;
                        $filters['userCopies']['readingList']++;
                    }
                }
                if ($userBook == "Others") {
                    if (in_array($book['isbn'], $userBooks['Others']))
                    {
                        $filters['userCopies']['requested']++;
                    }
                }
            }
        }

        if ($book['digital']) {
            $filters['userCopies']['hasDigital']++;
        }
        if ($book['tag'] == 'isNew') {
            $filters['userCopies']["isNew"]++;
        }
        if ($book['tag'] == 'isMostPopular') {
            $filters['userCopies']['isMostPopular']++;
        }
        if ($book['tag'] == 'isTopSeller') {
            $filters['userCopies']['isTopSeller']++;
        }
        if ($book['StudentRessource']) {
            $filters['userCopies']['HasStudentResources']++;
        }
        if ($book['InstructorRessource']) {
            $filters['userCopies']['HasProfessorResources']++;
        }
        /** Get Year of the book */
        if ($book['date'])
        {
            try {
                $year = (new \DateTime($book['date']))->format('Y');
                if (!isset($filters['years'][$year])) {
                    $filters['years'][$year] = 0;
                }
                $filters['years'][$year]++;
                $today = new \DateTime();

                if ((new \DateTime($book['date']))->format('Y-m-d') <= $today->format('Y-m-d')) {

                    $filters['years']['published']++;
                }
                if (($year === $today->format('Y')) && (new \DateTime($book['date']))->format('Y-m-d') > $today->format('Y-m-d') || ($year > $today->format('Y'))) {
                    $filters['years']['soon']++;
                }
            } catch (\Exception $e) {
            }
        }
    }


    /**
     * return parsed book's subcategories fromm book raw data
     * @param stdClass $book
     * @return array
     */
    public function getBookSubCategories(\stdClass $book): array
    {
        $subcategories = $book->Disciplines->Discipline ?? [];
        if (is_object($subcategories)) {
            $subcategories = [$subcategories];
        }
        $result = [];
        foreach ($subcategories as $subcategory) {
            if (isset($subcategory->TopdisciplineCode)) {
                $result[] = [
                    'name' => $subcategory->Description,
                    'code' => $subcategory->Code,
                    'parent' => $subcategory->TopdisciplineCode,
                    'topDiscipline' => $subcategory->Topdiscipline
                ];
            }
        }
        return $result;
    }

    /**
     * Sort array of books by one of his attributes
     * @param $books
     * @param $orderBy
     * @param string $sortOrder
     * @return array
     */
    private function sortBooks(array $books, string $orderBy, string $sortOrder = SORT_ASC): array
    {
        $order = strtolower($sortOrder) == 'asc' ? SORT_ASC : SORT_DESC;
        return (isset($books[key($books)][$orderBy])) ? MainHelper::sortArrayObject($books, $orderBy, $order) : $books;
    }

    /**
     * Adopt or renew user book for a certain course
     * @param $userId
     * @param array $adoption
     * @return array
     */
    public function setBookAdoption($userId, $adoption): array
    {
        $params = [
            'idsite' => Config::read('currentSiteId'),
            'userid' => $userId,
            'adoptionid' => '',
            'productid' => '',
            'adoptionStatus' => '',
            'reclevel' => '',
            'type' => '',
            'reason' => '',
            'othertitle' => '',
            'comments' => '',
            'course' => '',
            'coursecode' => '',
            'courselevel' => '',
            'students' => '',
            'bookusedreason' => '',
            'currentBook' => '',
            'degreeTitle' => '',
            'startdate' => '',
            'enddate' => '',
            'coursename' => ''
        ];
        foreach ($adoption as $key => $value) {
            if ($key === 'Status') {
                $params['adoptionStatus'] = $adoption['Status'] ? $this->closedWon :$this->closedLost;
            } elseif (array_key_exists(strtolower($key), $params)) {
                $params[strtolower($key)] = $value;
            } else {
                /** Do nothing */
            }
        }
        /** Reformat date to be compatible with slx date format */
        $params['startdate'] = $this->reCreateDateFromString($params['startdate']);
        $params['enddate'] = $this->reCreateDateFromString($params['enddate']);
        $params['currentBook'] = $adoption['CurrentBook'];
        $params['coursename'] = $adoption['InstitutionCourseName'];
        $params['coursecode'] = $adoption['Coursecode'];
        $params['degreeTitle'] = $adoption['DegreeTitle'];
        $response = $this->getSoap('elsSet_BookAdoption', $params, false);
        return (array)$response->elsSet_BookAdoptionResult;
    }

    /**
     * Send feedback for a specific adoption to slx
     * @param $userId
     * @param array $feedBack
     * @return array
     */
    public function setBookFeedBack($userId, $feedBack): array
    {
        $params = [
            'idsite' => Config::read('currentSiteId'),
            'userid' => $userId,
            'productid' => $feedBack['productId'],
            'adoptionid' => $feedBack['adoptionId'],
            'feedback' => $feedBack['message'],
            'allowsharing' => $feedBack['public'],
            'allowname' => $feedBack['name_public']
        ];
        $response = $this->getSoap('elsSet_BookFeedback', $params, false);
        return (array)$response->elsSet_BookFeedbackResult;
    }

    /**
     * Send feedback for a specific adoption to slx
     * @param $user User
     * @param array $feedBack
     * @return array
     */
    public function setBookReview($user, $feedBack): array
    {
        $params = [
            'idsite' => Config::read('currentSiteId'),
            'userid' => $user->getUserId(),
            'institution' => $user->getPrimaryInstitution()?$user->getPrimaryInstitution()->getInstitutionName():'',
            'isbn' => $feedBack['isbn'],
            'feedback' => $feedBack['message'],
            'allowsharing' => $feedBack['public'],
            'allowname' => $feedBack['name_public'],
            'rating' => $feedBack['rating']
        ];
        $response = $this->getSoap('elsSet_BookFeedback', $params, false);
        return (array)$response->elsSet_BookFeedbackResult;
    }

    /**
     * @param $userId
     * @param $feedBackId
     * @param $like
     * @return array
     */
    public function setBookReviewLike($userId, $feedBackId, $like): array
    {
        $params = [
            'idsite' => Config::read('currentSiteId'),
            'userid' => $userId,
            'feedbackid' => $feedBackId,
            'likes' => ($like=="true") ? 1 : 0
        ];
        $response = $this->getSoap('elsSet_BookFeedbackLike', $params, false);
        return (array)$response->elsSet_BookFeedbackLikeResult;
    }

    /**
     * Send feedback for a specific adoption to slx
     * @param $userId
     * @param $isbn
     * @return array
     */
    public function getAllBookReviews($userId, $isbn): array
    {
        $params = [
            'idsite' => Config::read('currentSiteId'),
            'userid' => $userId,
            'productid' => $isbn
        ];
        $response = $this->getSoap('elsGet_AllBookFeedback', $params, false);

        $feedBacks = [];
        if ($response->elsGet_AllBookFeedbackResult->Result &&
            isset($response->elsGet_AllBookFeedbackResult->FeedbackList->FeedBack)) {
            if (is_array($response->elsGet_AllBookFeedbackResult->FeedbackList->FeedBack)) {
                $feedBacks = $response->elsGet_AllBookFeedbackResult->FeedbackList->FeedBack;
            } else {
                $feedBacks[] = $response->elsGet_AllBookFeedbackResult->FeedbackList->FeedBack;
            }
        }
        return $feedBacks;
    }

    /**
     * Get book feedback by adoption & product id
     * @param $userId
     * @param $productId
     * @param $adoptionId
     * @return array
     */
    public function getBookFeedBack($userId, $productId, $adoptionId = '')
    {
        $params = [
            'idsite' => Config::read('currentSiteId'),
            'userid' => $userId,
            'productid' => $productId,
            'adoptionid' => $adoptionId,
        ];
        $response = $this->getSoap('elsGet_BookFeedback', $params, false);
        $feedBacksArray = [];
        if ($response->elsGet_BookFeedbackResult->Result &&
            isset($response->elsGet_BookFeedbackResult->FeedbackList->FeedBack)) {
            if (is_array($response->elsGet_BookFeedbackResult->FeedbackList->FeedBack)) {
                $feedBacks = $response->elsGet_BookFeedbackResult->FeedbackList->FeedBack;
            } else {
                $feedBacks[] = $response->elsGet_BookFeedbackResult->FeedbackList->FeedBack;
            }
            /** Remove duplicated feedback */
            $feedBacksArray = [];
            foreach ($feedBacks as $feedBack) {
                $feedBacksArray[(new DateTime($feedBack->Feedbackdate))->format('Y-m-d H')] = [
                    'feedbackDate' => $feedBack->Feedbackdate,
                    'feedbackText' => $feedBack->Feedback
                ];
            }
            /** Sort feedback by date */
            usort($feedBacksArray, function ($firstFeed, $secondFeed) {
                return strtotime($firstFeed['feedbackDate']) - strtotime($secondFeed['feedbackDate']);
            });
        }
        return $feedBacksArray;
    }

    /**
     *
     * @param $date
     * @return false|string
     */
    public function reCreateDateFromString($date)
    {
        if (!$date) {
            return null;
        }
        $dateObj = explode('-', $date);
        return date(DATE_ATOM, mktime(0, 0, 0, $dateObj[1], $dateObj[0], $dateObj[2]));
    }

    public function getAllUserBooks($userId): array
    {
        $response = $this->getSoap(
            'elsGet_UserBooksExtended',
            ['idsite' => Config::read('currentSiteId'), 'userid' => $userId]
        );
        $userBooks = array(
            'Approved' => [],
            'Adopted' => [],
            'Others' => []
        );
        if ($response->elsGet_UserBooksExtendedResult->Result) {
            $userBookList = $response->elsGet_UserBooksExtendedResult->List;
            if (!is_array($userBookList->ContactBook)) {
                $contactBooks[] = $userBookList->ContactBook;
            } else {
                $contactBooks = $userBookList->ContactBook;
            }
            foreach ($contactBooks as $contactBook) {
                $copies = [];
                if (isset($contactBook->Copies->ActiveBook)) {
                    if (!is_array($contactBook->Copies->ActiveBook)) {
                        $copies[] = $contactBook->Copies->ActiveBook;
                    } else {
                        $copies = $contactBook->Copies->ActiveBook;
                    }
                }
                foreach ($copies as $copy) {
                    if ($copy->Status != 'Cancelled') {
                        if ($copy->Status == 'Adopted') {
                            if (!in_array($copy->Isbn, $userBooks['Adopted'])) {
                                array_push($userBooks['Adopted'], $copy->Isbn);
                            }

                        } else if ($copy->Status == 'Approved') {
                            if (!in_array($copy->Isbn, $userBooks['Approved'])) {
                                array_push($userBooks['Approved'], $copy->Isbn);
                            }

                        } else {
                            if (!in_array($copy->Isbn, $userBooks['Others']) && !in_array($copy->Isbn, $userBooks['Approved']) && !in_array($copy->Isbn, $userBooks['Adopted'])) {
                                array_push($userBooks['Others'], $copy->Isbn);
                            }
                        }
                    }
                }
            }
        }
        return $userBooks;
    }

    public function getBooksHistory(string $userId, $icPage = false): array
    {
        $response = $this->getSoap(
            'elsGet_UserBooksHistory',
            ['idsite' => Config::read('currentSiteId'), 'userid' => $userId]
        );
        $userBooks = array();
        if ($response->elsGet_UserBooksHistoryResult->Result) {
            $userBookList = $response->elsGet_UserBooksHistoryResult->List;
            if (!is_array($userBookList->ActiveBook)) {
                $contactBooks[] = $userBookList->ActiveBook;
            } else {
                $contactBooks = $userBookList->ActiveBook;
            }
            if (!empty($userBookList)) {
                if ($icPage) {
                    return [count($contactBooks)];
                } else {
                    foreach ($contactBooks as $copy) {
                        $endDate = isset($copy->Enddate) ? explode(' ', $copy->Enddate) : '';
                        $enabledDate = isset($copy->Enableddate) ? explode(' ', $copy->Enableddate) : '';
                        $userBook = array(
                            'Isbn' => $copy->Isbn,
                            'Status' => $copy->Status,
                            'Format' => $copy->Format,
                            'PreOrder' => $copy->Preorder,
                            'EndDate' => $copy->Format === 'Digital' && $endDate[0] !== '' ?
                                DateTime::CreateFromFormat("m/d/Y", $endDate[0])->format('d-m-Y') : '',
                            'EnabledDate' => $enabledDate[0],
                            'SiteId' => $copy->Siteid,
                            'Site' => $copy->Site,
                            'ProductId' => $copy->Productid,
                            'ExternalBookId' => $copy->Format === 'Digital' ? $copy->Externalbookid : '',
                            'SentDate' => $copy->Format === 'Print' ? $copy->SentDate : '',
                            'Title' => $copy->Title,
                            'Author' => $copy->Author,
                            'Institutions' => (isset($copy->InstitutionList) ?
                                json_decode(json_encode($copy->InstitutionList), true) : [])
                        );

                        array_push($userBooks, $userBook);
                    }
                }
            }
        }
        return $userBooks;
    }

    /**
     * @param $userId
     * @return array
     * return list of Approved and awaiting approval books for the user with the given id
     */
    public function getRequestedBooks($userId): array
    {
        $response = $this->getSoap(
            'elsGet_UserBooksExtended',
            ['idsite' => Config::read('currentSiteId'), 'userid' => $userId]
        );
        $userBooks = array();
        if ($response->elsGet_UserBooksExtendedResult->Result) {
            $userBookList = $response->elsGet_UserBooksExtendedResult->List;
            if (!is_array($userBookList->ContactBook)) {
                $contactBooks[] = $userBookList->ContactBook;
            } else {
                $contactBooks = $userBookList->ContactBook;
            }
            foreach ($contactBooks as $contactBook) {
                $copies = [];
                if (isset($contactBook->Copies->ActiveBook)) {
                    if (!is_array($contactBook->Copies->ActiveBook)) {
                        $copies[] = $contactBook->Copies->ActiveBook;
                    } else {
                        $copies = $contactBook->Copies->ActiveBook;
                    }
                }
                foreach ($copies as $copy) {
                    $requestDate = isset($copy->Requesteddate) ? explode('T', $copy->Requesteddate) : array();

                    $userBook = array(
                        'Isbn' => $copy->Isbn,
                        'Format' => $copy->Format,
                        'StartDate' => $copy->Coursestartdate,
                        'SentDate' => $copy->Format === 'Print' ? $copy->SentDate : '',
                        'RequestedDate' => count($requestDate) > 0 ?
                            DateTime::CreateFromFormat("Y-m-d", $requestDate[0])->format('d-m-Y') : '',
                    );
                    if ($copy->Status == 'Pending' || $copy->Status == 'Approved') {
                        $userBooks[$copy->Isbn] = $userBook;
                    }
                }
            }
        }
        return $userBooks;
    }

    /**
     * check if the book has ancillary or not
     * @param $isbn
     * @return bool
     */
    public function hasStudentResources($isbn)
    {

        $response = $this->getSoap(
            'elsGet_CatalogItem',
            ['idsite' => Config::read('currentSiteId'), 'isbn' => $isbn],
            true
        );
        if (!isset($response->elsGet_CatalogItemResult->ErrorCode) && $response->elsGet_CatalogItemResult->PpmXML) {
            if (isset($response->elsGet_CatalogItemResult->Ancillary)) {
                if(isset($response->elsGet_CatalogItemResult->Ancillary->ProductAncillary)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * check if the book has ancillary for instrutor or not
     * @param $isbn
     * @return bool
     */
    public function hasInstructorResources($isbn)
    {

        /** Get content of bucket by credentials */
        $s3 = new S3Client([
            'version' => 'latest',
            'region' => 'eu-west-1',
            'credentials' => array(
                'key' => Config::read('S3_BUCKET_KEY'),
                'secret' => Config::read('S3_BUCKET_SECRET'),
            )
        ]);
        /** Get ancillary list */
        $results = $s3->listObjects([
            'Bucket' => 'icancillary',
            'Prefix' => "$isbn/"
        ])->search('Contents');
        if ($results)
        {
            return true;

        }
        return false;
    }

    /**
     * Return shared book for a user from slx WS
     * @param $reg
     * @param $lang
     * @param $userId
     * @param $start
     * @param $numItems
     * @return ArrayCollection
     * @throws NotFoundException
     */
    public function getSharedBooks(string $lang, string $userId, int $start= null, int $numItems = null ): array
    {
        $response = $this->getSoap(
            'els_GetReadingRecommendations',
            ['idsite' => Config::read('currentSiteId'), 'userid' => $userId,'received' => 1, 'start' => $start, 'numitems' => $numItems]
        );

        $sharedBooks = new ArrayCollection();
        if (!$response->els_GetReadingRecommendationsResult->Result) {
            return [$sharedBooks, 0];
        }
        $foundBooks = [];
        if(isset($response->els_GetReadingRecommendationsResult->ReadingRecommendations) &&
            isset($response->els_GetReadingRecommendationsResult->ReadingRecommendations->Recommendation)) {
            $foundBooks = $response->els_GetReadingRecommendationsResult->ReadingRecommendations->Recommendation;
        }

        $arrayRecommendations = [];
        if(!is_array($foundBooks)) {
           array_push($arrayRecommendations,$foundBooks);
        }
        else {
            $arrayRecommendations = $foundBooks;
        }

        foreach ($arrayRecommendations as $recommendations)
        {
            $book = new Book();
            if( $recommendations->RecommendedBy) {
                $book->setRecommendedBy($recommendations->RecommendedBy);
            }
            if ($recommendations->CatalogProduct) {
                if (isset($recommendations->CatalogProduct->AvailableTypes)) {
                    $availableTypes = $recommendations->CatalogProduct->AvailableTypes;
                    if (is_array($availableTypes->string)) {
                        foreach ($availableTypes->string as $val) {
                            $book->addAvailableType($val);
                        }
                    } else {
                        $book->addAvailableType($availableTypes->string);
                    }
                }
                unset($recommendations->CatalogProduct->AvailableTypes);
                $book = $this->addBooksSetter($book,$recommendations->CatalogProduct,$lang);
                if (isset($recommendations->CatalogProduct)){
                    $book->setTag($this->getBookTag($recommendations->CatalogProduct->IsNew, $recommendations->CatalogProduct->IsMostPopular, $recommendations->CatalogProduct->IsTopSeller, $recommendations->CatalogProduct->HasUpdatedEdition));
                }
                 $sharedBooks->add($book);
            }
        }

        return [$sharedBooks,$response->els_GetReadingRecommendationsResult->NumberOfRecords];
    }


    /**
     * Share the book with the given isbn with users according to the list of given emails
     * @param string $userId
     * @param array $emails
     * @param string $message
     * @param string $isbn
     * @return bool
     */
    public function shareBookWithUsers(string $userId, array $emails, string $message, string $isbn):bool
    {
        $response = $this->getSoap(
            'els_SetReadingRecommendation',
            ['idsite' => Config::read('currentSiteId'),'userid' => $userId, 'isbn' => $isbn, 'emails' => $emails,'message' => $message]
        );
        if($response->els_SetReadingRecommendationResult->Result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Delete recommended book from list of you my like  for the user with the given id
     * @param string $userId
     * @param string $isbn
     * @return bool
     */
    public function deleteRecommendation(string $userId, string $isbn):bool
    {
        $response = $this->getSoap(
            'els_Del_UserRecomendation',
            ['idsite' => Config::read('currentSiteId'),'userid' => $userId, 'isbn' => $isbn]
        );
        if($response->els_Del_UserRecomendationResult->Result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Delete the shared book with the given isbn for the logged in user
     * @param string $userId
     * @param string $isbn
     * @return bool
     */
    public function deleteSharedRecommendation(string $userId, string $isbn):bool
    {
        $response = $this->getSoap(
            'els_Del_ReadingRecomendation',
            ['idsite' => Config::read('currentSiteId'),'userid' => $userId, 'isbn' => $isbn]
        );
        if($response->els_Del_ReadingRecomendationResult->Result) {
            return true;
        } else {
            return false;
        }
    }

    public function getUserRequestedBooks($userId): array
    {
        $response = $this->getSoap(
            'elsGet_UserBooksExtended',
            ['idsite' => Config::read('currentSiteId'), 'userid' => $userId]
        );
        $userBooks = array();
        if ($response->elsGet_UserBooksExtendedResult->Result) {
            $userBookList = $response->elsGet_UserBooksExtendedResult->List;
            if (!is_array($userBookList->ContactBook)) {
                $contactBooks[] = $userBookList->ContactBook;
            } else {
                $contactBooks = $userBookList->ContactBook;
            }
            foreach ($contactBooks as $contactBook) {
                $responseBook = $this->getSoap(
                    'elsGet_CatalogItem',
                    ['idsite' => Config::read('currentSiteId'), 'isbn' => $contactBook->Isbn],
                    true
                );
                if (!isset($responseBook->elsGet_CatalogItemResult->ErrorCode) && $responseBook->elsGet_CatalogItemResult->PpmXML) {
                    $book = new Book(simplexml_load_string($responseBook->elsGet_CatalogItemResult->PpmXML));
                    array_push($userBooks, $book);
                }
            }
        }
        return $userBooks;
    }

    /**
     * @param $isbn
     * @param $userId
     * @return true
     * This function call w webservice elsSet_UserEvent to save book that user have open to do a tracking for users favorite books
     */
    public function saveUserEvent ($isbn, $userId, $eventType):bool {
        $userEventResponse = $this->getSoap(
            'elsSet_UserEvent',
            [
                'idsite' => Config::read('currentSiteId'),
                'userid' => $userId,
                'isbn' => $isbn,
                'type' => $eventType,
                'filename' => ''
            ],
            true
        );
        if (isset($userEventResponse->elsSet_UserEventResult)) {
          return $userEventResponse->elsSet_UserEventResult->Result;
        }
        return true;
    }

    /**
     * @param $isNew
     * @param $isMostPopular
     * @param $isTopSeller
     * @param $hasUpdatedEdition
     * @return string
     * Return only one tag for the book
     */
    public function getBookTag($isNew, $isMostPopular, $isTopSeller, $hasUpdatedEdition):string {
        $tag = '';
        //any book have tag news
        if($isNew) {
            $tag = 'isNew';
        }
        //any don't have the new tag but have the MostPopular tag
        if(!$isNew && $isMostPopular) {
            $tag = 'isMostPopular';
        }
        //book don't have new tag and don't have MostPopular tag but have the tag TopSeller
        if(!$isNew && !$isMostPopular && $isTopSeller) {
            $tag = 'isTopSeller';
        }

        //only Most popular
        if (!$isNew && !$isTopSeller && !$hasUpdatedEdition &&  $isMostPopular) {
            $tag = 'isMostPopular';
        }
        //only TopSeller
        if (!$isNew && $isTopSeller &&  !$isMostPopular && !$hasUpdatedEdition) {
            $tag = 'isTopSeller';
        }
        //only Updated_edition
        if (!$isNew && !$isTopSeller &&  !$isMostPopular && $hasUpdatedEdition) {
            $tag = 'updatedEdition';
        }
        return $tag;
    }
}
