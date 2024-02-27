<?php
/**
 * Created by PhpStorm.
 * User: medNaceur
 * Date: 21/06/2019
 * Time: 14:43
 */

namespace MBComponents\Helpers;
use Exception;
use lib\Config;

/**
 * This class wil contain all helpful function and code
 * Class MainHelper
 * @package MBComponents\Helpers
 */
class MainHelper
{
    /**
     * Order an array of object by a given attribute to that object
     * @param $array
     * @param $objectAttribute
     * @param int $order
     * @return array
     */
    public static function sortArrayObject(array $array, string $objectAttribute, int $order = SORT_ASC): array
    {
        $result = array();
        $sortableArray = array();
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $objectAttribute) {
                        $sortableArray[$k] = strtoupper($v2);
                    }
                }
            } else {
                $sortableArray[$k] = strtoupper($v);
            }
        }
        $order == SORT_ASC ? asort($sortableArray) :  arsort($sortableArray);
        foreach ($sortableArray as $k => $v) {
            $result[] = $array[$k];
        }
        return $result;
    }

    public static function getClientIpAddress($serverParams) {
        return $serverParams['HTTP_CLIENT_IP']??$serverParams['HTTP_X_FORWARDED_FOR']??$serverParams['HTTP_X_FORWARDED']
            ??$serverParams['HTTP_FORWARDED_FOR']??$serverParams['REMOTE_ADDR']??'0.0.0.0';
    }

    /**
     * @param $url
     * @param bool $json
     * @return bool|string
     * @throws Exception
     */
    public static function getData($url,$json=false)
    {
        $ch = curl_init();
        $timeout = 5;
        $sanitizedUrl = filter_var($url, FILTER_SANITIZE_URL);
        curl_setopt($ch,CURLOPT_URL,$sanitizedUrl);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_USERPWD, Config::read('apiUserId') . ":" . Config::read('apiLicenceKey'));
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
        curl_setopt($ch, CURLOPT_FAILONERROR, true); // Required for HTTP error codes to be reported via our call to curl_error($ch)
        $responseData = curl_exec($ch);
        if(curl_errno($ch)) {
            throw new Exception(' error calling :https://geoip.maxmind.com/geoip/v2.1/country/', 400);
        }
        curl_close($ch);
        if($json){
            return json_decode($responseData,true);
        }

        return $responseData;
    }
}
