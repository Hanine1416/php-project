<?php

/*
 * This file is part of the Inspection Copy.
 * Copyright (C) 2023 Elsevier.
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
 * Class bookNew
 * @package MainBundle\Entity
 * @ORM\Table(name="books_new")
 * @ORM\Entity(repositoryClass="MainBundle\Repository\BooksNewRepository")
 */
class BooksNew
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
     * @ORM\Column(name="isbn",type="string")
     */
    private $isbn;

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
    public function getIsbn(): string
    {
        return $this->isbn;
    }

    /**
     * @param string $isbn
     */
    public function setIsbn(string $isbn): void
    {
        $this->isbn = $isbn;
    }
}
