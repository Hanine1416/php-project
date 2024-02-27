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

class Address
{

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     * for En => Address
     * for Br => complemento
     * for Es => Dirección
     */
    private $address1;

    /**
     * for En => Address(Cont)
     * for Br => Endereço
     * for Es => Dirección (Cont)
     * @var string
     */
    private $address2;

    /**
     * for En => Building room number
     * for Br => N°
     * @var string
     */
    private $address3;

    /**
     * for Br => Bairro
     * @var string
     */
    private $address4;

    /**
     * @var string
     */
    private $postalCode;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $country;

    /**
     * @var string
     */
    private $description;

    /**
     * @var bool
     */
    private $isPrimary;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $salutation;

    /**
     * @var bool
     */
    private $canBeDeleted;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $phone;

    /**
     * Institution constructor.
     */
    public function __construct()
    {
        $this->isPrimary=false;
    }

    /**
     * @return string
     */
    public function getId(): ?string
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
    public function getAddress1(): ?string
    {
        return $this->address1;
    }

    /**
     * @param string $address1
     */
    public function setAddress1(string $address1): void
    {
        $this->address1 = $address1;
    }

    /**
     * @return string
     */
    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    /**
     * @param string $address2
     */
    public function setAddress2(string $address2): void
    {
        $this->address2 = strlen($address2)>30?substr($address2, 0, 30):$address2;
    }

    /**
     * @return string
     */
    public function getAddress3(): ?string
    {
        return $this->address3;
    }

    /**
     * @param string $address3
     */
    public function setAddress3(string $address3): void
    {
        $this->address3 = strlen($address3)>30?substr($address3, 0, 30):$address3;
    }

    /**
     * @return string
     */
    public function getAddress4(): ?string
    {
        return $this->address4;
    }

    /**
     * @param string $address4
     */
    public function setAddress4(string $address4): void
    {
        $this->address4 = $address4;
    }

    /**
     * @return string
     */
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     */
    public function setPostalCode(string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @return string
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }


    /**
     * @param $country
     */
    public function setCountry($country): void
    {
        if (is_a($country, "stdclass"))
        {
            $country=$country->Text;
        }
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
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
     * @return bool
     */
    public function isPrimary(): ?bool
    {
        return $this->isPrimary;
    }

    /**
     * @param bool $isPrimary
     */
    public function setIsPrimary(bool $isPrimary): void
    {
        $this->isPrimary = $isPrimary;
    }

    /**
     * @return string
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState(string $state): void
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getSalutation(): ?string
    {
        return $this->salutation;
    }

    /**
     * @param string $salutation
     */
    public function setSalutation(string $salutation): void
    {
        $this->salutation = $salutation;
    }

    /**
     * @return bool
     */
    public function getCanBeDeleted(): ?bool
    {
        return $this->canBeDeleted;
    }

    /**
     * @param bool $canBeDeleted
     */
    public function setCanBeDeleted(bool $canBeDeleted): void
    {
        $this->canBeDeleted = $canBeDeleted;
    }


    /**
     * @return null|string
     */
    public function getType(): ?string
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
     * @return string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }
}
