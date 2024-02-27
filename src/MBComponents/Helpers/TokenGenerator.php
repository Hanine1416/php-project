<?php
/**
 * Created by PhpStorm.
 * User: mobelite
 * Date: 27/03/2018
 * Time: 10:07
 */

namespace MBComponents\Helpers;

/**
 * Class TokenGenerator
 * @package MBComponents\Helpers
 */
class TokenGenerator
{
    /**
     * generate a random token
     * @return string
     */
    public static function generateToken():string
    {
        $randomBytes = random_bytes(5);
        $timestamp = date('d-m-Y') . time();
        return hash('sha256', $randomBytes . $timestamp);
    }
}
