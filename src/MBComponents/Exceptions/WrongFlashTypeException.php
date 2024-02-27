<?php
/**
 * Created by PhpStorm.
 * User: mobelite
 * Date: 31/05/2019
 * Time: 10:03
 */

namespace MBComponents\Exceptions;

/**
 * Class WrongFlashTypeException
 * @package MBComponents\Exceptions
 */
class WrongFlashTypeException extends \Exception
{
    /**
     * SoapException constructor.
     * @param string $message
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct(
        $message='',
        $code = 400,
        \Exception $previous = null
    ) {
        if (!$message) {
            $message = 'The type should be (success/error)';
        }
        parent::__construct($message, $code, $previous);
    }
}
