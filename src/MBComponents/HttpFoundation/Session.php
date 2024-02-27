<?php

/**
 * Session controller class
 */

namespace MBComponents\HttpFoundation;

use lib\Config;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

/**
 * Override symfony http session
 * Class Session
 * @package controllers
 */
class Session extends \Symfony\Component\HttpFoundation\Session\Session
{
    /**
     * Session constructor.
     * @param SessionStorageInterface|null $storage
     * @param AttributeBagInterface|null $attributes
     * @param FlashBagInterface|null $flashes
     */
    public function __construct(
        SessionStorageInterface $storage = null,
        AttributeBagInterface $attributes = null,
        FlashBagInterface $flashes = null
    ) {
        /** Create session folder if not found */
        if (!is_dir(SESSION_DIRECTORY)) {
            mkdir(SESSION_DIRECTORY, 0777, true);
        }

        $storage =  new NativeSessionStorage(
            array(
                'cookie_httponly' => true
            ),
            new NativeFileSessionHandler((SESSION_DIRECTORY)),
            $this->getSession_Storage_MetadataBagService()
        );

        parent::__construct($storage, $attributes, $flashes);
    }

    /**
     * Gets the private 'session.storage.metadata_bag' shared service.
     *
     * @return \Symfony\Component\HttpFoundation\Session\Storage\MetadataBag
     */
    protected function getSession_Storage_MetadataBagService()
    {
        return new \Symfony\Component\HttpFoundation\Session\Storage\MetadataBag('ic_meta', '0');
    }
}
