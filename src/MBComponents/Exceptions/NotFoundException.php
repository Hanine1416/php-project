<?php
/**
 * This file is part of the IC.
 * Created by mobelite.
 * Date: 5/28/18
 * Time: 12:56
 * @author: Mobelite <www.mobelite.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MBComponents\Exceptions;

/**
 * Class NotFoundException
 * @package MBComponents\Exceptions
 */
class NotFoundException extends \Exception
{
    /**
     * NotFoundException constructor.
     * @param string $message
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($message='', $code = 404, \Exception $previous = null)
    {
        if (!$message) {
            $message = 'The page you requested could not be found';
        }
        parent::__construct($message, $code, $previous);
    }
}
