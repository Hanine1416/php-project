<?php
/**
 * This file is part of the Inspection Copy.
 * Created by mobelite.
 * Date: 3/29/18
 * Time: 16:37
 * @author: Mobelite <www.mobelite.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MBComponents\Services;

use lib\Config;

/**
 * Class GoogleReCaptcha
 * @package MBComponents\Services
 */
class GoogleReCaptcha
{
    /**
     * @param $response
     * @return mixed
     */
    public function validate($response)
    {
        $postData = http_build_query(
            array(
                'secret' => Config::read('recaptchaPrivate'),
                'response' => $response,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            )
        );
        $opts = array('http' =>
            array(
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postData
            )
        );
        $context = stream_context_create($opts);
        $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
        $result = json_decode($response);
        return $result->success;
    }
}
