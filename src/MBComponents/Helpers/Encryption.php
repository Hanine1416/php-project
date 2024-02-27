<?php
/**
 * Created by PhpStorm.
 * User: mobelite
 * Date: 28/03/2019
 * Time: 16:18
 */

namespace MBComponents\Helpers;

use lib\Config;

class Encryption
{
    public static $method = "AES-128-ECB";

    /**
     * @param string $password
     * @return mixed
     */
    public static function encrypt(string $password) :string
    {
        return openssl_encrypt($password, Encryption::$method, Config::read('secret_key'));
    }

    /**
     * @param string $password
     * @return mixed
     */
    public static function decrypt(string $password) :string
    {
        return openssl_decrypt($password, Encryption::$method, Config::read('secret_key'));
    }
}
