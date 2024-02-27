<?php

/*
 * This file is part of the Inspection Copy.
 * Copyright (C) 2019 Elsevier.
 * Created by mobelite.
 *
 * Date: 4/11/18
 * Time: 17:38
 * @author: Mobelite <www.mobelite.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace UserBundle\Entity;

use DOctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Exclude;
use MBComponents\Helpers\TokenGenerator;

/**
 * Class CookiePage
 * @package UserBundle\CookieTable
 * @ORM\Table(name="cookie_table")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\CookieTableRepository")
 */
class CookieTable
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id" ,type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Exclude()
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=64 , name="token")
     */
    private $token;

    /**
     * @var string
     * @ORM\Column(name="service_name", type="string" , length=255)
     */
    private $serviceName;

    /**
     * @var string
     * @ORM\Column(name="cookie_names", type="string" , length=255)
     * @Accessor(getter="getCookieNames")
     */
    private $cookieNames;

    /**
     * @var string
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var string
     * @ORM\Column(name="more_info", type="text")
     */
    private $moreInfo;

    /**
     * @var bool
     * @ORM\Column(name="position", type="boolean")
     */
    private $enable;

    /**
     * @var string
     * @ORM\Column(name="language", type="string",length=10)
     */
    private $language;

    /**
     * CookieTable constructor.
     */
    public function __construct()
    {
        $this->token = TokenGenerator::generateToken();
        $this->enable=true;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    /**
     * @param string $serviceName
     */
    public function setServiceName(string $serviceName): void
    {
        $this->serviceName = $serviceName;
    }

    /**
     * @return array
     */
    public function getCookieNames(): array
    {
        return unserialize($this->cookieNames);
    }

    /**
     * @param array $cookieName
     */
    public function setCookieNames(array $cookieName): void
    {
        $this->cookieNames = serialize($cookieName);
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getMoreInfo(): string
    {
        return $this->moreInfo;
    }

    /**
     * @param string $moreInfo
     */
    public function setMoreInfo(string $moreInfo): void
    {
        $this->moreInfo = $moreInfo;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    /**
     * @return bool
     */
    public function isEnable(): bool
    {
        return $this->enable;
    }

    /**
     * @param bool $enable
     */
    public function setEnable(bool $enable): void
    {
        $this->enable = $enable;
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @param string $language
     */
    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }
}
