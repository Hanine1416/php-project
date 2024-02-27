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

namespace MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Cover
 * @package MainBundle\Entity
 * @ORM\Table(name="cover")
 * @ORM\Entity(repositoryClass="MainBundle\Repository\CoverRepository")
 */
class Cover
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
     * @ORM\Column(name="language" ,type="string", length=3)
     */
    private $language;

    /**
     * @var string
     * @ORM\Column(name="image",type="text",nullable=true)
     */
    private $image;

    /**
     * @var string
     * @ORM\Column(name="profession",type="string",length=100)
     */
    private $profession;

    /**
     * @var int
     * @ORM\Column(name="position",type="smallint")
     */
    private $position;

    /**
     * @var \DateTime
     * @ORM\Column(name="edit_date",type="datetime")
     */
    private $editDate;

    /**
     * @var string
     * @ORM\Column(name="category",type="string")
     */
    private $category;

    public function __construct(string $language)
    {
        $this->language=$language;
        $this->editDate=new \DateTime();
    }

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

    /**
     * @return string
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * @param string $image
     */
    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    /**
     * @return string
     */
    public function getLanguage(): ?string
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
    public function getProfession(): ?string
    {
        return $this->profession;
    }

    /**
     * @param string $profession
     */
    public function setProfession(string $profession): void
    {
        $this->profession = $profession;
    }

    /**
     * @return \DateTime
     */
    public function getEditDate(): ?\DateTime
    {
        return $this->editDate;
    }

    /**
     * @param \DateTime $editDate
     */
    public function setEditDate(\DateTime $editDate): void
    {
        $this->editDate = $editDate;
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
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @param int $position
     */
    public function setCategory(string $category): void
    {
        $this->category = $category;
    }
}
