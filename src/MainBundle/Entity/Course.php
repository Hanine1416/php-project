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

class Course
{

    /** @var string $id */
    private $id;

    /** @var string $text */
    private $text;

    /** @var string $shortText */
    private $shortText;

    /** @var string $itemId */
    private $itemId;

    /** @var string $picklistId */
    private $picklistId;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getShortText(): string
    {
        return $this->shortText;
    }

    /**
     * @param string $shortText
     */
    public function setShortText(string $shortText): void
    {
        $this->shortText = $shortText;
    }

    /**
     * @return string
     */
    public function getItemId(): string
    {
        return $this->itemId;
    }

    /**
     * @param string $itemId
     */
    public function setItemId(string $itemId): void
    {
        $this->itemId = $itemId;
    }

    /**
     * @return string
     */
    public function getPicklistId(): string
    {
        return $this->picklistId;
    }

    /**
     * @param string $picklistId
     */
    public function setPicklistId(string $picklistId): void
    {
        $this->picklistId = $picklistId;
    }
}
