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

/**
 * Class Institution
 * @package UserBundle\Entity
 */
class Institution
{
    /**
     * @var $id
     */
    private $id;

    /**
     * @var string
     */
    private $institutionId;

    /**
     * @var string
     */
    private $institutionName;

    /**
     * @var string
     */
    private $departmentId;

    /**
     * @var string
     */
    private $departmentName;

    /**
     * @var string
     */
    private $profession;

    /**
     * @var string
     */
    private $speciality;

    /**
     * @var bool
     */
    private $isPrimary;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var bool $hasRequest
     */
    private $hasRequest;

    /**
     * Institution constructor.
     */
    public function __construct()
    {
        $this->enabled=true;
        $this->isPrimary=false;
        $this->hasRequest=false;
    }

    /**
     * @return string
     */
    public function getInstitutionName(): ?string
    {
        return $this->institutionName;
    }

    /**
     * @param string $institutionName
     */
    public function setInstitutionName(string $institutionName): void
    {
        $this->institutionName = $institutionName;
    }

    /**
     * @return string
     */
    public function getDepartmentName(): ?string
    {
        return $this->departmentName;
    }

    /**
     * @param string departmentName
     */
    public function setDepartmentName(string $departmentName): void
    {
        $this->departmentName = $departmentName;
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
     * @return string
     */
    public function getSpeciality(): ?string
    {
        return $this->speciality;
    }

    /**
     * @param string $speciality
     */
    public function setSpeciality(string $speciality): void
    {
        $this->speciality = $speciality;
    }

    /**
     * @return string
     */
    public function getDepartmentId(): ?string
    {
        return $this->departmentId;
    }

    /**
     * @param string $departmentId
     */
    public function setDepartmentId(?string $departmentId): void
    {
        $this->departmentId = $departmentId;
    }

    /**
     * @return string
     */
    public function getInstitutionId(): ?string
    {
        return $this->institutionId;
    }

    /**
     * @param string $institutionId
     */
    public function setInstitutionId(?string $institutionId): void
    {
        $this->institutionId = $institutionId;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
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
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return bool
     */
    public function isHasRequest(): ?bool
    {
        return $this->hasRequest;
    }

    /**
     * @param bool $hasRequest
     */
    public function setHasRequest(bool $hasRequest): void
    {
        $this->hasRequest = $hasRequest;
    }
}
