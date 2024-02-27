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

use Doctrine\Common\Collections\ArrayCollection;
use MainBundle\Entity\BookInstitutionRequest;

class BookRequest
{

    /**
     * @var string $bookIsbn
     */
    private $bookIsbn;

    /**
     * @var string $bookType
     */
    private $bookFormat;

    /**
     * @var ArrayCollection<BookInstitutionRequest> $institutionsRequest;
     */
    private $institutions;
    /**
     * @var boolean $preOrder
     */
    private $preOrder;

    /**
     * @var boolean $addressId
     */
    private $addressId;


    /**
     * BookRequest constructor.
     */
    public function __construct()
    {
        $this->preOrder=false;
        $this->institutions = new ArrayCollection();
    }

    /**
     * @return bool
     */
    public function isPreOrder(): bool
    {
        return $this->preOrder;
    }

    /**
     * @param bool $preOrder
     */
    public function setPreOrder(bool $preOrder): void
    {
        $this->preOrder = $preOrder;
    }
    /**
     * @return string
     */
    public function getAddressId(): ?string
    {
        return $this->addressId;
    }

    /**
     * @param string $addressId
     */
    public function setAddressId(string $addressId): void
    {
        $this->addressId = $addressId;
    }

    /**
     * @return string
     */
    public function getBookIsbn(): ?string
    {
        return $this->bookIsbn;
    }

    /**
     * @param string $bookIsbn
     */
    public function setBookIsbn(string $bookIsbn): void
    {
        $this->bookIsbn = $bookIsbn;
    }

    /**
     * @return string
     */
    public function getBookFormat(): ?string
    {
        return $this->bookFormat;
    }

    /**
     * @param string $bookFormat
     */
    public function setBookFormat(string $bookFormat): void
    {
        $this->bookFormat = $bookFormat;
    }

    /**
     * @return ArrayCollection<BookInstitutionRequest>
     */
    public function getInstitutions(): ArrayCollection
    {
        return $this->institutions;
    }

    /**
     * @param ArrayCollection $institutionsRequest
     */
    public function setInstitutions(ArrayCollection $institutionsRequest): void
    {
        $this->institutions= $institutionsRequest;
    }
}
