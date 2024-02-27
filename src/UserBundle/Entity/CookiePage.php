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
 * Class CookiePage
 * @package UserBundle\CookiePage
 * @ORM\Table(name="cookie_page")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\CookiePageRepository")
 */
class CookiePage
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id" ,type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=64 , name="token")
     */
    private $token;

    /**
     * @var string
     * @ORM\Column(name="section_title", type="text")
     */
    private $topSection;

    /**
     * @var string
     * @ORM\Column(name="section_content", type="text")
     */
    private $bottomSection;

    /**
     * @var string
     * @ORM\Column(name="table_elements", type="text")
     */
    private $tableElements;


    public function __construct(string $language)
    {
        $this->token = TokenGenerator::generateToken();
        $this->language = $language;
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
    public function getTopSection(): ?string
    {
        return $this->topSection;
    }

    /**
     * @param string $topSection
     */
    public function setTopSection(string $topSection): void
    {
        $this->topSection = $topSection;
    }

    /**
     * @return string
     */
    public function getBottomSection(): ?string
    {
        return $this->bottomSection;
    }

    /**
     * @param string $bottomSection
     */
    public function setBottomSection(string $bottomSection): void
    {
        $this->bottomSection = $bottomSection;
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
     * @var string
     * @ORM\Column(name="language", type="string",length=10)
     */
    private $language;

    /**
     * @return int
     */
    public function getId(): ?int
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

    public function getTableElements()
    {
        return unserialize($this->tableElements);
    }

    /**
     * @param array $tableElements
     */
    public function setTableElements(array $tableElements): void
    {
        $this->tableElements = serialize($tableElements);
    }
}
