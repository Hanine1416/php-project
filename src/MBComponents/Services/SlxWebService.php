<?php
/**
 * Created by PhpStorm.
 * User: mobelite
 * Date: 28/03/2018
 * Time: 09:18
 */

namespace MBComponents\Services;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use lib\Config;
use stdClass;
use UserBundle\Entity\Notification;

/**
 * This class help to use all available slx ws
 * Class SlxWebService
 * @package MBComponents\Services
 */
class SlxWebService extends SOAP {
    /** @var array $countries */
    private $countries = [];

    /**
     * Return user id from slx by email if exist
     * @param $email
     * @return array
     */
    public function findUserByEmail($email): ?array {
        $response = $this->getSoap(
            'elsGet_UserID',
            ['idsite' => Config::read('currentSiteId'), 'email' => $email]
        );
        /**  Verify if response is valid */
        if ($response->elsGet_UserIDResult->Result && $response->elsGet_UserIDResult)
        {
            /** Return user id with site code (from where the user registered) */
            $output['userId'] = $response->elsGet_UserIDResult->Reply;
            if (isset($response->elsGet_UserIDResult->CreateUser))
            {
                $output['source'] = $response->elsGet_UserIDResult->CreateUser;
            }
            return $output;
        }
        return null;
    }

    /**
     * Return user notifications
     * @param string $userId
     * @return ArrayCollection
     * @throws Exception
     */

    public function getAllNotifications(string $userId, $lang, $reg): ArrayCollection {
        /** Configure the parameters with user id */
        $params = [
            'idsite' => Config::read('currentSiteId'),
            'userid' => $userId,
            'language' => $lang,
            'region' => $reg
        ];
        /** Call slx ws get notifications */
        $response = $this->getSoap('elsGet_UserNotifications', $params, false);
        $notifications = new ArrayCollection();
        if ($response->elsGet_UserNotificationsResult->Result && isset($response->elsGet_UserNotificationsResult))
        {
            $eventType = $response->elsGet_UserNotificationsResult;
            /** Create a notification with correspond event type */
            $this->addUserNotification($notifications, 'DigitalExpired', $eventType->DigitalAboutExpire);
            $this->addUserNotification($notifications, 'DigitalApproved', $eventType->DigitalApproved);
            $this->addUserNotification($notifications, 'PrintApproved', $eventType->PrintApproved);
            $this->addUserNotification($notifications, 'DigitalAvailable', $eventType->DigitalAvailable);
            $this->addUserNotification($notifications, 'PrintStatusChanged', $eventType->PrintStatusChanged);
            $this->addUserNotification($notifications, 'FeedbackDue', $eventType->FeedbackDue);
            $this->addUserNotification($notifications, 'ConfirmationDue', $eventType->ConfirmationDue);
            $this->addUserNotification($notifications, 'Recomendations', $eventType->Recomendations);
            $this->addUserNotification($notifications, 'NewUserRecommendations', $eventType->NewUserRecommendations);
            $this->addUserNotification($notifications, 'PublishedBooks', $eventType->PublishedBooks);
            $this->addUserNotification($notifications, 'NewEditions', $eventType->NewEditions);
        }
        return $notifications;
    }

    /**
     * Map user notifications form slx to notification object with correspond event name
     * @param $notifications
     * @param $eventName
     * @param $events
     * @throws Exception
     */
    public function addUserNotification(ArrayCollection $notifications, $eventName, $events): void {
        /** @var AppService $appService */
        $appService = $this->container->get(AppService::class);
        if (isset($events->UserEvent))
        {
            /** Check if it is a single or array of events */
            if (is_array($events->UserEvent))
            {
                $events = $events->UserEvent;
            } else
            {
                $events = [$events->UserEvent];
            }
            /** Create notification object for each notification event */
            foreach ($events as $event)
            {
                $notification = new Notification();
                $notification->setIsbn($event->Isbn);
                $notification->setEventType($eventName);
                $notification->setDate(new \DateTime($event->Date));
                $notification->setIsRead($event->Isread);
                $notification->setTitle($event->Title);
                if($eventName == "Recomendations") {
                    $notification->setLink($appService->generateUrl('showBookDetails', ['isbn' => $event->Isbn]));
                    $notification->setDetails($event->Details);
                } else {
                    $notification->setLink($appService->generateUrl('my-books'));
                }
                $notifications->add($notification);
            }
        }
    }

    /**
     * Mark user notification as read
     * updateUserNotification
     * @param string $userId
     * @throws Exception
     */

    public function updateUserNotification(string $userId): void {
        $params = [
            'idsite' => Config::read('currentSiteId'),
            'userid' => $userId,
            'isread' => 'true',
            'updateall' => 'true',
            'date' => (new DateTime())->format('Y-m-d')
        ];
        $this->getSoap('elsUpdate_UserNotificationStatus', $params);
    }

    /**
     * Update user information in slx
     * The user already exist
     * @param array $userInfo
     * @return mixed
     */
    public function updateUserInfo($userInfo) {
        /** Call slx ws with user info to replace them with the old data */
        $result = $this->getSoap('elsSet_User', array(
            'idsite' => Config::read('currentSiteId'),
            'userid' => $userInfo['Userid'],
            'userdata' => $userInfo
        ), false);

        return $result->elsSet_UserResult;
    }

    public function getRestrictedEmails($lang, $reg) {
        $result = [];
        $response = $this->getSoap(
            'elsGet_PickList',
            [
                'idsite' => Config::read('currentSiteId'),
                'picklistName' => 'IC Restricted Domains',
                'language' => $lang,
                'region' => $reg
            ]
        );

        if ($response->elsGet_PickListResult->Result)
        {
            foreach ($response->elsGet_PickListResult->ItemList->PickListItem as $email)
            {
                if($email->Text !== "\t")
                {
                    $result[] = trim(str_replace("\t", ' ', $email->Text));
                }
            }
        }
        return $result;
    }

    /**
     * Return country list by language
     * @param $lang
     * @param $region
     * @return array
     */
    public function getCountries($lang,$region): array {
        /** Call slx ws that return available country for a given language */
        $response = $this->getSoap(
            'elsGet_PickList',
            [
                'idsite' => Config::read('currentSiteId'),
                'picklistName' => 'Country Codes',
                'region' => $region,
                'language' => $lang
            ],
            true
        );
        /** Sorting data */
        if (!isset($response->elsGet_PickListResult->ErrorCode))
        {
            /** Check if the slx ws returned only one country soo map it to array to process data */
            if (isset($response->elsGet_PickListResult->ItemList->PickListItem) && is_array($response->elsGet_PickListResult->ItemList->PickListItem))
            {
                $this->countries = $response->elsGet_PickListResult->ItemList->PickListItem;
            } else
            {
                $this->countries[$response->elsGet_PickListResult->ItemList->PickListItem->Id] =
                    $response->elsGet_PickListResult->ItemList->PickListItem;
            }
        }
        return $this->countries;
    }

    /**
     * Return Global Account Manager by country
     * @param $country
     * @return String
     */
    public function GetCountryManager($country): ?string {

        $parameters = [
            'idsite' => Config::read('currentSiteId'),
            'countrycode' => $country
        ];
        $response = $this->getSoap('elsGet_CountryManager',$parameters, true);

        /**  Verify if response is valid */
        if ($response->elsGet_CountryManagerResult->Result)
        {
            /** Return user id with site code (from where the user registered) */
            return $response->elsGet_CountryManagerResult->AMemail;
        }
         return '';
    }

    /**
     * Return country code region
     * @param $country
     * @param $lang
     * @param $countryCode
     * @return string
     */
    public function getRegionByCountry($country, $lang, $countryCode=null): string {
        if (!$countryCode)
        {
            /** Get country Iso from it's name exp United Kingdom => UK */
            $countryCode = $this->getCountryIso($country);
        }
        /** Call ws get code region for current site language */
        $response = $this->getSoap(
            'elsGet_PickList',
            ['idsite' => Config::read('currentSiteId'), 'picklistName' => 'CountryRegionCodes'], true);
        if ($response->elsGet_PickListResult->Result && $countryCode)
        {
            foreach ($response->elsGet_PickListResult->ItemList->PickListItem as $countryData)
            {
                if (strtolower($countryData->Text) === strtolower($countryCode))
                {
                   return $countryData->Shorttext;
                }
            }
        }
        return '7';
    }

    /**
     * Return country short code (ISO) by name from slx like Tunisia => TN
     * @param $userCountry
     * @return string
     */
    public function getCountryIso($userCountry): ?string {
        /** Fix country name to be knowing to slx  */
        switch (strtoupper($userCountry))
        {
            case 'INDIA':
                $res = 'IN';
                break;
            case 'BRAZIl':
            case 'BRASIL':
                $res = 'BR';
                break;
            case 'FRANCE':
                $res = 'FR';
                break;
            case 'México':
                $res = 'Mexico';
                break;
            default:
                $res = '';
                break;
        }
        if ($res !== '')
        {
            return $res;
        }
        /** Call slx webservice for getting country region code  */
        $response = $this->getSoap(
            'elsGet_PickList',
            [
                'idsite' => Config::read('currentSiteId'),
                'picklistName' => 'Country Codes',
                'region' => '',
            ],
            true
        );
        /** Check if the webservice returned a valid response */
        if ($response->elsGet_PickListResult->Result)
        {
            /** If single item was found map it to array */
            if (!is_array($response->elsGet_PickListResult->ItemList->PickListItem))
            {
                $res = $response->elsGet_PickListResult->ItemList->PickListItem->Text == $userCountry ?
                    $response->elsGet_PickListResult->ItemList->PickListItem->Shorttext : '';
            } else
            {
                /** Search for the desired country and extract it code region from object shortText */
                foreach ($response->elsGet_PickListResult->ItemList->PickListItem as $country)
                {
                    if ($country->Text == $userCountry)
                    {
                        $res = $country->Shorttext;
                        break;
                    }
                }
            }
        }
        return $res;
    }

    /**
     * Return list of institutes for specific country and city
     * @param $country
     * @param string $city
     * @param string $zipCode
     * @param string $state
     * @return array
     */
    public function getInstitutions($country, $city = '', $zipCode = '',  $state = ''): array {
        /** Configure the ws parameters */
        $parameters = [
            'idsite' => Config::read('currentSiteId'),
            'country' => $country
        ];
        /** Check if the city was provided */
        $city !== '' ? $parameters['city'] = $city: null;
        /** Check if the zipCode was provided */
        $zipCode !== ''? $parameters['postalcode'] = $zipCode : null;
        /** Check if the state was provided */
        $state!=='' ? $parameters['state'] = $state : null;
        /** Call get institution webservice */
        $response = $this->getSoap('elsGet_Institutions', $parameters, true);
        $output = [];
        /** Sort data */
        if ($response->elsGet_InstitutionsResult->Result &&
            property_exists($response->elsGet_InstitutionsResult->ItemList, "PickListItem"))
        {
            $pickListItem = $response->elsGet_InstitutionsResult->ItemList->PickListItem;
            /** Check if slx return one institution or array of institutions */
            if (!is_array($pickListItem))
            {
                $output[$pickListItem->Id] = [
                    'id' =>$pickListItem->Id,
                    'name'=>$pickListItem->Text,
                    'type'=>$pickListItem->Type
                ];
            } else
            {
                /** Index array result by institution id*/
                foreach ($pickListItem as $val)
                {
                    $output[$val->Id] = ['id' =>$val->Id,'name'=>$val->Text,'type'=>$val->Type];
                }
            }
        }
        return $output;
    }

    /**
     * Return list of department for specific institution
     * @param $institutionId
     * @param string $profession
     * @return array
     */
    public function getDepartments($institutionId, $profession=""): array {
        /** Configure the ws parameters */
        $parameters = [
            'idsite' => Config::read('currentSiteId'),
            'institutionid' => $institutionId
        ];
        if (!empty($profession))
        {
            $parameters['studytype'] = $profession;
        }
        /** Call get department webservice */
        $response = $this->getSoap('elsGet_Departments', $parameters, true);
        $output = [];
        /** Sort data */
        if (!isset($response->elsGet_DepartmentsResult->ErrorCode) &&
            isset($response->elsGet_DepartmentsResult->ItemList->PickListItem))
        {
            /** Check if slx return one department or array of departments */
            if (!is_array($response->elsGet_DepartmentsResult->ItemList->PickListItem))
            {
                $output[$response->elsGet_DepartmentsResult->ItemList->PickListItem->Id] =
                    $response->elsGet_DepartmentsResult->ItemList->PickListItem->Text;
            } else
            {
                /** Index array result by department id*/
                foreach ($response->elsGet_DepartmentsResult->ItemList->PickListItem as $val)
                {
                    $output[$val->Id] = $val->Text;
                }
            }
        }
        return $output;
    }

    /**
     * Return a specific country's cities from local json file
     * @param $country
     * @param bool $fromFile
     * @return array
     */
    public function getCities($country,$fromFile=true): array {
        /** Load cities data from local city.json file */
        $data = [];
        if ($fromFile) {
            $json = json_decode(file_get_contents(__DIR__.'/../../../city.json'), true);
            /** Search for the entered country and return it's cities */
            if (!empty($country) && array_key_exists($country, $json) && is_array($json[$country]))
            {
                foreach ($json[$country] as $value)
                {
                    $data[] = $value;
                }
                /** Sort city by name */
                array_multisort($json[$country], SORT_ASC, $data);
            }
        } else
        {
            /** Call get department webservice for indian website*/
            $response = $this->getSoap(
                'elsGet_PickList',
                ['idsite' => Config::read('currentSiteId'), 'picklistName' => 'City','language' => 'IN'],
                true
            );
            $cities = $response->elsGet_PickListResult->ItemList->PickListItem;
            $cities = is_array($cities)?$cities:[$cities];
            foreach ($cities as $city)
            {
                if (strtolower($country) === strtolower($city->Shorttext))
                {
                    $data[] = $city->Text;
                }
            }
        }
        return $data;
    }

    /**
     * Return a specific country's cities from local json file
     * @param $country
     * @param $region
     * @param $lang
     * @return array
     */
    public function getStates($country, $region, $lang): array {
        /** Load cities data from local city.json file */
        $data = [];
        /** Call get department webservice */
        $params = [
            'idsite' => Config::read('currentSiteId'),
            'picklistName' => 'State',
            'language' => $lang,
            'region' => $region
        ];
        $response = $this->getSoap('elsGet_Picklist', $params, true);
        $states = $response->elsGet_PickListResult->ItemList->PickListItem;
        $states = is_array($states)?$states:[$states];
        foreach ($states as $state)
        {
           /* if (strtolower($country) === strtolower($state->Shorttext)) {*/
                $data[] = $state->Text;
            /*}*/
        }
        return $data;
    }

    /**
     * return list of specialities for a specific profession
     * @param $profession
     * @param string $lang
     * @return array
     */
    public function getSpecialities($profession, $lang = 'en'): array {
        /** Configure parameter to call the speciality ws */
        $parameters = [
            'idsite' => Config::read('currentSiteId'),
            'Profession' => $profession,
            'type' => 'Lecturer/Instructor',
            'country' => strtoupper($lang),
            'lang' => strtoupper($lang)
        ];
        /** Call the gey speciality webservice */
        $response = $this->getSoap('elsGet_Speciality', $parameters, true);
        $output = [];
        /** Sort data */
        if (!isset($response->elsGet_SpecialityResult->ErrorCode))
        {
            $responseList = $response->elsGet_SpecialityResult->List;
            /** Check if the webservice return a single item then map it to array */
            if (!is_array($responseList->string))
            {
                $output[$responseList->string] =
                    $responseList->string;
            } else
            {
                /** Reindex the result array with specialty name */
                foreach ($responseList->string as $val)
                {
                    $output[$val] = $val;
                }
            }
        }
        /** Return list of specialities */
        return $output;
    }

    /**
     * Return department addresses from SLX
     * @param $departmentId
     * @return stdClass
     */
    public function getAddress($departmentId): ?stdClass {
        /** Configure parameter to call the address ws */
        $parameters = [
            'idsite' => Config::read('currentSiteId'),
            'entityid' => $departmentId,
            'addressid' => '',
        ];
        /** Call get address webservice */
        $response = $this->getSoap('elsGet_Address', $parameters, true);
        $output = null;
        /** Sort data */
        if (!isset($response->elsGet_AddressResult->ErrorCode) && isset($response->elsGet_AddressResult))
        {
            $output = $response->elsGet_AddressResult;
        }
        return $output;
    }

    /**
     * Return country region language
     * @param $country
     * @return array
     */
    public function getLanguageRegionFromCountry($country): array {
        /** Call slx webservice that return  region  language foreach country */
        $response = $this->getSoap(
            'elsGet_PickList',
            ['idsite' => Config::read('currentSiteId'), 'picklistName' => 'CountryRegionLanguage'],
            true
        );

        if (!isset($response->elsGet_PickListResult->ErrorCode) &&
            is_array($response->elsGet_PickListResult->ItemList->PickListItem))
        {
            foreach ($response->elsGet_PickListResult->ItemList->PickListItem as $item)
            {
                /** Item->Text contain 3 data info in the same time separated with & : Language&CountryCode&countryName*/
                $data = explode('&',$item->Text);
                if (in_array(strtolower($country), [strtolower(trim($data[2])), strtolower(trim($data[1]))], true))
                {
                    return [
                        'region' => $item->Shorttext,
                        'language' => strtolower($data[0]),
                        'country' => strtolower(trim($data[2]))
                    ];
                }
            }
        }
        /** Return default language & region */
        return [
            'region' => '7',
            'language' => 'en',
            'country' => 'United kingdom'
        ];
    }

    /**
     * @param $feedback
     * @param $userId
     * Save the feedback given by the user about the release version
     * @return boolean
     */
    public function saveFeedback($feedback, $userId) {
        /** Call slx webservice to save user */
        $response = $this->getSoap(
            'elsSet_BookFeedback',
            ['idsite' => Config::read('currentSiteId'),
                'userid' => $userId,
                'feedback' => $feedback,
                'allowsharing' =>0,
                'allowname' => 0 ],
            true
        );
        if ($response->elsSet_BookFeedbackResult)
        {
            return $response->elsSet_BookFeedbackResult->Result;
        }
        return false;
    }
}
