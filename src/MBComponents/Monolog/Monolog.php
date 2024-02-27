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

use MBComponents\HttpFoundation\Request;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Class Monolog
 * @package MBComponents\Monolog
 */
class Monolog implements MonologInterface
{
    /** @var Logger $log */
    private $log;
    /** @var Request $request */
    private $request;

    /**
     * Monolog constructor.
     * @param Request $request
     * @param string $channelName
     */
    public function __construct(Request $request, $channelName = 'default')
    {
        $this->request = $request;
        // create a log channel
        $this->log = new Logger($channelName);
    }

    /**
     * @param $method
     * @param string|array $fileName
     * @param string $message
     * @param array $data
     */
    public function log($method, $fileName, $message, array $data = [])
    {
        if (is_array($fileName)) {
            foreach ($fileName as $file) {
                $this->pushHandler($file);
            }
        } else {
            $this->pushHandler($fileName);
        }
        $data['ipAddress'] = $this->request->getClientIp();
        $this->log->$method($message, $data);
    }

    /**
     * @param $file
     */
    private function pushHandler($file)
    {
        /**
         * create a Json formatter
         */
        $formatter = new LineFormatter();

        $fileName = LOG_DIRECTORY . '/mono_log/' . $file . '.log';

        /**
         * create a handler
         */
        $stream = new StreamHandler($fileName);
        $stream->setFormatter($formatter);

        /** bind */
        $this->log->pushHandler($stream);
    }
}
