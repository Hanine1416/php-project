<?php
/**
 * This file is part of the IC.
 * Created by mobelite.
 * Date: 5/28/18
 * Time: 13:03
 * @author: Mobelite <www.mobelite.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MBComponents\Exceptions;

use Exception;

/**
 * Class SoapException
 * @package MBComponents\Exceptions
 */
class SoapException extends \Exception
{
    /**
     * SoapException constructor.
     * @param string $message
     * @param int $code
     * @param Exception $previous
     */
    public function __construct(
        $message='',
        $code = 400,
        \Exception $previous = null
    ) {
        if (!$message) {
            $message = 'Bad Request (Soap API Execution)';
        }
        parent::__construct($message, $code, $previous);
    }
}
