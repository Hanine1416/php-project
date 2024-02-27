<?php
/**
 * This file is part of the Inspection Copy.
 * Created by mobelite.
 * Date: 3/30/18
 * Time: 12:11
 * @author: Mobelite <www.mobelite.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MBComponents\Helpers;

use MBComponents\Exceptions\WrongFlashTypeException;

/**
 * Class MessagesFlashHelper
 * @package MBComponents\Helpers
 */
class MessagesFlashHelper
{
    /**
     * @var string
     */
    const STORAGE_KEY = 'flashes';

    /**
     * @var array $flashes
     */
    private $flashes = [];

    /**
     * @param $type
     * @param $message
     * @throws \Exception
     */
    public function addFlash($type, $message) :void
    {
        if (!in_array($type, ['success', 'error'])) {
            throw new WrongFlashTypeException('The type should be (success/error)');
        }

        if (!array_key_exists(MessagesFlashHelper::STORAGE_KEY, $_SESSION)) {
            $_SESSION[MessagesFlashHelper::STORAGE_KEY] = [];
        }

        $this->flashes[$type][] = $message;
        $_SESSION[MessagesFlashHelper::STORAGE_KEY] = $this->flashes;
    }

    /**
     * @return mixed
     */
    public function getMessages()
    {
        if (!array_key_exists(MessagesFlashHelper::STORAGE_KEY, $_SESSION)) {
            $_SESSION[MessagesFlashHelper::STORAGE_KEY] = [];
        }

        $output = $_SESSION[MessagesFlashHelper::STORAGE_KEY];
        $_SESSION[MessagesFlashHelper::STORAGE_KEY] = [];
        return $output;
    }

    /**
     * @param $key
     * @return array
     * @throws \Exception
     */
    public function getMessage($key)
    {
        if (!in_array($key, ['success', 'error'])) {
            throw new WrongFlashTypeException();
        }
        $output = [];

        if (array_key_exists(MessagesFlashHelper::STORAGE_KEY, $_SESSION)) {
            $messages = $_SESSION[MessagesFlashHelper::STORAGE_KEY];
            if (array_key_exists($key, $messages)) {
                $output = $messages[$key];
                unset($messages[$key]);
                $_SESSION[MessagesFlashHelper::STORAGE_KEY] = $messages;
            }
        }

        return $output;
    }
}
