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
use MBComponents\Helpers\TokenGenerator;

/**
 * Class Banner
 * @package UserBundle\Entity
 * @ORM\Table(name="banner")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\BannerRepository")
 */
class Banner
{
    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="string", length=64 , name="token",unique=true)
     */
    private $token;

    /**
     * @var string
     * @ORM\Column(name="title_en", type="text")
     */
    private $titleEN;
    /**
     * @var string
     * @ORM\Column(name="title_de", type="text")
     */
    private $titleDE;
    /**
     * @var string
     * @ORM\Column(name="title_es", type="text")
     */
    private $titleES;
    /**
     * @var string
     * @ORM\Column(name="title_fr", type="text")
     */
    private $titleFR;
    /**
     * @var string
     * @ORM\Column(name="title_anz", type="text")
     */
    private $titleANZ;

    /**
     * @var string
     * @ORM\Column(name="content_en", type="text")
     */
    private $contentEN;

    /**
     * @var string
     * @ORM\Column(name="content_es", type="text")
     */
    private $contentES;
    /**
     * @var string
     * @ORM\Column(name="content_de", type="text")
     */
    private $contentDE;
    /**
     * @var string
     * @ORM\Column(name="content_fr", type="text")
     */
    private $contentFR;
    /**
     * @var string
     * @ORM\Column(name="content_anz", type="text")
     */
    private $contentANZ;

    /**
     * @var string
     * @ORM\Column(name="type", type="text")
     */
    private $type;

    /**
     * @var bool
     * @ORM\Column(name="is_enabled", type="boolean")
     */
    private $isEnabled;

    /**
     * @var bool
     * @ORM\Column(name="is_closed", type="boolean")
     */
    private $isClosed;

    public function __construct()
    {
        $this->token = TokenGenerator::generateToken();
    }
    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getTitleEN(): string
    {
        return $this->titleEN;
    }

    /**
     * @param string $titleEN
     */
    public function setTitleEN(string $titleEN): void
    {
        $this->titleEN = $titleEN;
    }

    /**
     * @return string
     */
    public function getTitleDE(): string
    {
        return $this->titleDE;
    }

    /**
     * @param string $titleDE
     */
    public function setTitleDE(string $titleDE): void
    {
        $this->titleDE = $titleDE;
    }

    /**
     * @return string
     */
    public function getTitleES(): string
    {
        return $this->titleES;
    }

    /**
     * @param string $titleES
     */
    public function setTitleES(string $titleES): void
    {
        $this->titleES = $titleES;
    }

    /**
     * @return string
     */
    public function getTitleFR(): string
    {
        return $this->titleFR;
    }

    /**
     * @return string
     */
    public function getTitleANZ(): string
    {
        return $this->titleANZ;
    }

    /**
     * @param string $titleANZ
     */
    public function setTitleANZ(string $titleANZ): void
    {
        $this->titleANZ = $titleANZ;
    }

    /**
     * @param string $titleFR
     */
    public function setTitleFR(string $titleFR): void
    {
        $this->titleFR = $titleFR;
    }

    /**
     * @return string
     */
    public function getContentEN(): string
    {
        return $this->contentEN;
    }

    /**
     * @param string $contentEN
     */
    public function setContentEN(string $contentEN): void
    {
        $this->contentEN = $contentEN;
    }

    /**
     * @return string
     */
    public function getContentES(): string
    {
        return $this->contentES;
    }

    /**
     * @param string $contentES
     */
    public function setContentES(string $contentES): void
    {
        $this->contentES = $contentES;
    }

    /**
     * @return string
     */
    public function getContentDE(): string
    {
        return $this->contentDE;
    }

    /**
     * @param string $contentDE
     */
    public function setContentDE(string $contentDE): void
    {
        $this->contentDE = $contentDE;
    }

    /**
     * @return string
     */
    public function getContentFR(): string
    {
        return $this->contentFR;
    }

    /**
     * @param string $contentFR
     */
    public function setContentFR(string $contentFR): void
    {
        $this->contentFR = $contentFR;
    }

    /**
     * @return string
     */
    public function getContentANZ(): string
    {
        return $this->contentANZ;
    }

    /**
     * @param string $contentANZ
     */
    public function setContentANZ(string $contentANZ): void
    {
        $this->contentANZ = $contentANZ;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function getIsEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * @param bool $isEnabled
     */
    public function setIsEnabled(bool $isEnabled): void
    {
        $this->isEnabled = $isEnabled;
    }

    /**
     * @return bool
     */
    public function getIsClosed(): bool
    {
        return $this->isClosed;
    }

    /**
     * @param bool $isClosed
     */
    public function setIsClosed(bool $isClosed): void
    {
        $this->isClosed = $isClosed;
    }


}
