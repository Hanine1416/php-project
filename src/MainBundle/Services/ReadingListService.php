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

use Doctrine\Common\Collections\ArrayCollection;
use lib\Config;
use MBComponents\Exceptions\NotFoundException;
use MBComponents\Services\SlxWebService;

/**
 * Class ReadingListService
 * @package MainBundle\Services
 */
class ReadingListService extends SlxWebService
{

    /**
     * @param string $userId
     * @param string|null $readingListId
     * @return ArrayCollection
     */
    public function getReadingLists(string $userId, string $readingListId = null): ArrayCollection
    {
        $response = $this->getSoap(
            'elsGet_ReadingList',
            [
                'idsite' => Config::read('currentSiteId'),
                'userid' => $userId,
                'readingListID' => $readingListId
            ],
            false
        );
        $readingLists = new ArrayCollection();
        if ($response->elsGet_ReadingListResult->Result &&
            isset($response->elsGet_ReadingListResult->ReadingLists->ReadingList)) {
            $foundReadingLists = $response->elsGet_ReadingListResult->ReadingLists->ReadingList;
            if (!is_array($foundReadingLists)) {
                $foundReadingLists = [$foundReadingLists];
            }
            foreach ($foundReadingLists as $foundReadingList) {
                if (isset($foundReadingList->ListItems->ReadingListItem)) {

                    if (!is_array($foundReadingList->ListItems->ReadingListItem)) {
                        $foundReadingList->ListItems->ReadingListItem = [$foundReadingList->ListItems->ReadingListItem];
                    }
                }
                $readingLists->add($foundReadingList);
            }
        }

        return $readingLists;
    }

    /**
     * Return user Reading lists
     * @param string $userId
     * @param string $readingListId
     * @return array
     */
    public function getReadingList(string $userId, string $readingListId = null): array
    {
        $response = $this->getSoap(
            'elsGet_ReadingList',
            [
                'idsite' => Config::read('currentSiteId'),
                'userid' => $userId,
                'readingListID' => $readingListId
            ],
            false
        );
        $readingLists = [];
        if ($response->elsGet_ReadingListResult->Result) {
            $response = $response->elsGet_ReadingListResult->ReadingLists->ReadingList;
            if (is_array($response)) {
                $readingLists = $response;
            } else {
                $readingLists[] = $response;
            }
        }
        return $readingLists;
    }

    public function getReadingListBookContent($userId,$readingListId,$isbn) {
        $response = $this->getSoap(
            'elsGet_ReadingList',
            [
                'idsite' => Config::read('currentSiteId'),
                'userid' => $userId,
                'readingListID' => $readingListId
            ],
            false
        );
        if ($response->elsGet_ReadingListResult->Result) {
            $response = $response->elsGet_ReadingListResult->ReadingLists->ReadingList;
            $array = array_column((array)$response->ListItems->ReadingListItem,'Isbn');
            if(empty($array) && $response->ListItems->ReadingListItem->Isbn === $isbn )
            return $response->ListItems->ReadingListItem ;
            foreach ($response->ListItems->ReadingListItem as $book) {
                if($book->Isbn === $isbn) {
                    return $book ;
                }

        }
    }

    }

    /**
     * Set book to reading list
     * @param $userId
     * @param $readingListIsbn
     * @param $readingListID
     * @param string $section
     * @return array
     */
    public function setBookReadingList($userId, $readingListIsbn, $readingListID, $section = ''): array
    {
        $params = [
            'idsite' => Config::read('currentSiteId'),
            'userid' => $userId,
            'readingListID' => $readingListID,
            'isbn' => $readingListIsbn,
            'section' => $section
        ];

        $response = $this->getSoap('elsSet_ReadingListItem', $params, false);
        return (array)$response->elsSet_ReadingListItemResult;
    }

    /**
     * Set book to reading list
     * @param $userId
     * @param $readingList
     * @return array
     */
    public function setReadingList($userId, $readingList = null): array
    {
        $params = [
            'idsite' =>  Config::read('currentSiteId'),
            'userid' => $userId,
            'listName' => $readingList->get('readingListName'),
            'moduleName'=> $readingList->get('courseName'),
            'moduleCode'=>$readingList->get('courseCode'),
            'startDate' => $readingList->get('startDate'),
            'featured' => false,
            /** build user data from post request */
            'programList' => [
                'ReadingListProgram' => [
                    'program'=> '',
                    'code'=> $readingList->get('courseCode'),
                    'level' => $readingList->get('courseLevel'),
                    'accountID' => ''
                    ]
            ]
        ];
        $response = $this->getSoap('elsSet_ReadingList', $params, false);
        return (array)$response->elsSet_ReadingListResult;
    }

    /**
     * Set reading list name
     * @param $userId
     * @param $readingListID
     * @param $listName
     * @return array
     */
    public function setReadingListName($userId, $readingListID, $listName): array
    {
        $params = [
            'idsite' =>  Config::read('currentSiteId'),
            'userid' => $userId,
            'readingListID' => $readingListID,
            'listName'=> $listName
        ];
        $response = $this->getSoap('elsSet_ReadingListName', $params, false);
        return (array)$response->elsSet_ReadingListNameResult;
    }

    /**
     * @param $userId
     * @param $readingListID
     * @param $readingListIsbn
     * @return array
     */
    public function deleteReadingListItem($userId, $readingListID, $readingListIsbn)
    {

        $params = [
            'idsite' => Config::read('currentSiteId'),
            'userid' => $userId,
            'readingListID' => $readingListID,
            'isbn' => $readingListIsbn
        ];

        $response = $this->getSoap('elsDel_ReadingListItem', $params, false);
        return (array)$response->elsDel_ReadingListItemResult;
    }

    /**
     * @param $userId
     * @param $readingListIsbn
     * @return array
     * @throws \Exception
     */
    public function getReadingListsItem($userId, $readingListIsbn)
    {
        $readingListsItem = [];
        $readingLists = $this->getReadingLists($userId, null);
        foreach ($readingLists as $readingList) {
            if(isset($readingList->ListItems->ReadingListItem)) {
                foreach ($readingList->ListItems->ReadingListItem as $book) {
                    if ($book->Isbn === $readingListIsbn) {
                        $readingListsItem [] = $readingList->ReadingListID;
                    }
                }
            }
        }

        return $readingListsItem;
    }

    /**
     * Update book category
     *
     * @param [string] $userId
     * @param [string] $readingListID
     * @param [integer] $isbn
     * @param [string] $category
     * @return array
     */
    public function setReadingListItemCategory($userId, $readingListID,$isbn,$category): array
    {
        $params = [
            'idsite' => Config::read('currentSiteId'),
            'userid' => $userId,
            'readingListID' => $readingListID,
            'isbn' => $isbn,
            'section' => $category
        ];

        $response = $this->getSoap('elsSet_ReadingListItem', $params, false);
        return (array)$response->elsSet_ReadingListItemResult;
    }

    /**
     * @param $userId
     * @param $readingListID
     * @return array
     */
    public function deleteReadingList($userId,$readingListID): array
    {
        $params = [
            'idsite' => Config::read('currentSiteId'),
            'userid' => $userId,
            'readingListID' => $readingListID,
        ];

        $response = $this->getSoap('elsDel_ReadingList', $params, false);
        return (array)$response->elsDel_ReadingListResult;
    }


    /**
     * Save action do it by connected user on the reading list page
     * @param $userId
     * @param $readingListID
     * @param string $details
     * @return bool
     */
    public function saveHistory($userId, $readingListID, $details = ''): bool
    {
        $response = $this->getSoap(
            'elsSet_ReadingListHistory',
            [
                'idsite' => Config::read('currentSiteId'),
                'userid' => $userId,
                'readingListID' => $readingListID,
                'details' => $details
            ],
            false
        );
        return $response->elsSet_ReadingListHistoryResult->Result;
    }

    /**
     * return the history of the list with the given id for the connected user
     * @param string $userId
     * @param string $ReadingListID
     * @return string
     */
    function getReadingListHistory(string  $userId, string $ReadingListID ) {
        //get history for each reading list
        $responseHistory = $this->getSoap(
            'elsGet_ReadingListHistory',
            [
                'idsite' => Config::read('currentSiteId'),
                'userid' => $userId,
                'readingListID' => $ReadingListID
            ],
            false
        );
        $ReadingListHistory = [];
        if($responseHistory->elsGet_ReadingListHistoryResult->Result) {
           if(isset($responseHistory->elsGet_ReadingListHistoryResult->History->ReadingListHistory)) {
               if(is_array($responseHistory->elsGet_ReadingListHistoryResult->History->ReadingListHistory)) {
                   $ReadingListHistory = $responseHistory->elsGet_ReadingListHistoryResult->History->ReadingListHistory;
               } else {
                   $ReadingListHistory[0] = $responseHistory->elsGet_ReadingListHistoryResult->History->ReadingListHistory;
               }
           }
        }
        return $ReadingListHistory;
    }
}
