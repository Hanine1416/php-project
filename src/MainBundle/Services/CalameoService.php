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

use lib\Config;
use MBComponents\Exceptions\NotFoundException;
use UserBundle\Entity\User;

/**
 * this class help to communicate and extract data from calameo API
 * Class CalameoService
 * @package MainBundle\Services
 */
class CalameoService
{
    /** @var string $url */
    private $url = 'https://api.calameo.com/1.0';
    /** @var array $signature */
    private $signature =[];
    /** @var User $user */
    private $user;

    /**
     * CalameoService constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user=$user;
        $this->resetSignature($user);
    }

    /**
     Clean Returned Data
     */
    function cleanMe($input) {
        $input = htmlspecialchars($input, ENT_IGNORE, 'utf-8');
        return $input;
    }

    /**
     * get book information from calameo api
     * @param $isbn
     * @return array|null
     */
    public function getBookInfo($isbn): ?array
    {
        $this->signature['action'] ='API.getBookInfos';
        $this->signature['start']=0;
        $this->signature['step']=50;
        $this->signature['book_id']=$isbn;
        /** create signature from calameo info */
        $tmpArray = $this->signature;
        $tmpArray['apiKey']='apikey'.$tmpArray['apiKey'];
        $tmpArray['action']='action'.$tmpArray['action'];
        $tmpArray['subscriptionId']='subscription_id'.$tmpArray['subscriptionId'];
        $tmpArray['output']='output'.$tmpArray['output'];
        $tmpArray['login']='login'.$tmpArray['login'];
        $tmpArray['expiration']='expires'.$tmpArray['expiration'];
        $tmpArray['start']='start'.$tmpArray['start'];
        $tmpArray['step']='step'.$tmpArray['step'];
        $tmpArray['book_id']='book_id'.$tmpArray['book_id'];
        sort($tmpArray);
        $codedSignature = md5(implode('', $tmpArray));
        $jsonLink = sprintf(
            $this->url.
            '?apikey=%s&action=%s&expires=%s&login=%s&output=%s&start=%s&step=%s&subscription_id=%s&signature=%s&book_id=%s',
            $this->signature['apiKey'],
            $this->signature['action'],
            $this->signature['expiration'],
            $this->signature['login'],
            $this->signature['output'],
            $this->signature['start'],
            $this->signature['step'],
            $this->signature['subscriptionId'],
            $codedSignature,
            $this->signature['book_id']
        );

        $json = $this->cleanMe(file_get_contents(utf8_decode($jsonLink)));

        $decode = json_decode($json);
        if ($decode->response->status == 'error')
        {
            $response = null;
        } 
        else
        {
            $books = $decode->response->content;
            if ($isbn == $books->ID) 
            {
                $book['bookId'] = $books->ID;
                $book['title'] = $books->Name;
                $book['AccountID'] = $books->AccountID;
                return $book;
            }

            if (count($books) < $this->signature['step'])
            {
                $response = null;
            } else
                {
                $this->signature['start'] += $this->signature['step'];
                $response = $this->getBookInfo($isbn);
            }
        }
        return $response;
    }

    /**
     * get user account information from calameo api
     * @return array|null
     */
    public function getUserInfo(): ?array
    {
        $this->resetSignature($this->user);
        $this->signature['action']= 'API.getSubscriberInfos';

        /** create signature from calameo info */
        $tmpArray = $this->signature;
        $tmpArray['apiKey']='apikey'.$tmpArray['apiKey'];
        $tmpArray['action']='action'.$tmpArray['action'];
        $tmpArray['subscriptionId']='subscription_id'.$tmpArray['subscriptionId'];
        $tmpArray['output']='output'.$tmpArray['output'];
        $tmpArray['login']='login'.$tmpArray['login'];
        $tmpArray['expiration']='expires'.$tmpArray['expiration'];
        sort($tmpArray);
        $codedSignature = md5(implode('', $tmpArray));
        $jsonLinkSubscriberI = sprintf(
            $this->url."?apikey=%s&action=%s&expires=%s&login=%s&output=%s&subscription_id=%s&signature=%s",
            $this->signature['apiKey'],
            $this->signature['action'],
            $this->signature['expiration'],
            $this->signature['login'],
            $this->signature['output'],
            $this->signature['subscriptionId'],
            $codedSignature
        );
        $jsonSubscriberI = $this->cleanMe(file_get_contents(utf8_decode($jsonLinkSubscriberI)));
        $decodeSubscriberI = json_decode($jsonSubscriberI);
        if ($decodeSubscriberI->response->status == 'error')
        {
            return null;
        }
        $baseObjInfo = $decodeSubscriberI->response->content;
        return [
            'AccountID' => $baseObjInfo->AccountID,
            'login' => $baseObjInfo->Login,
            'password' => $baseObjInfo->Password,
            'SubscriptionID' => $baseObjInfo->SubscriptionID
        ];
    }

    /**
     * @param User $user
     */
    public function resetSignature(User $user): void
    {
        $this->signature=[
            'secretKey'=>Config::read('calameoSecretKey'),
            'apiKey'=>Config::read('calameoAPI'),
            'subscriptionId'=>Config::read('calameoSubscriptionId'),
            'output' => Config::read('calameoOutput'),
            'expiration'=>time() * 60 * 60,
            'login'=>$user->getUserId(),
        ];
    }
}
