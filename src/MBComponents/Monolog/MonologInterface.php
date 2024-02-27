<?php
/**
 * This file is part of the Inspection Copy.
 * Created by mobelite.
 * Date: 5/9/18
 * Time: 12:55
 * @author: Mobelite <www.mobelite.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MBComponents\Monolog;

/**
 * interface MonologInterface
 * @package MBComponents\Monolog
 */
interface MonologInterface
{
    /**
     * @param $method
     * @param $fileName
     * @param $message
     * @param array $data
     *
     * usage : $logger->log('info', 'forgotten-password', 'forgottenPasswordLinkOpened', [
     *      'processOutcome' => 'success',
     *      'userId' => $resetRequest->getUserIdentifier(),
     * ])
     *
     */
    public function log($method, $fileName, $message, array $data = []);
}
