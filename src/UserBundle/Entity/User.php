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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class User
 * @package UserBundle\Entity
 * @ORM\HasLifecycleCallbacks
 */
class User
{
    /**
     * @var string $createUser
     */
    private $createUser;
    /**
     * @var string $userId
     */
    private $userId = null;
    /**
     * @var string $cpf
     */
    private $cpf;
    /**
     * @var string $specialty
     */
    private $specialty;
    /**
     * @var string $profession
     */
    private $profession;
    /**
     * @var string $stage
     */
    private $stage;
    /**
     * @var string $institutionName
     */
    private $institutionName;
    /**
     * @var string $institutionId
     */
    private $institutionId;
    /**
     * @var string $departmentName
     */
    private $departmentName;
    /**
     * @var string $departmentId
     */
    private $departmentId;
    /**
     * @var string $amPhone
     */
    private $amPhone;
    /**
     * @var string $amEmail
     */
    private $amEmail;
    /**
     * @var string $amName
     */
    private $amName;
    /**
     * @var string $email
     */
    private $email;
    /**
     * @var string $mobile
     */
    private $mobile;
    /**
     * @var string $mainPhone
     */
    private $mainPhone;
    /**
     * @var string $country
     */
    private $country;
    /**
     * @var string $suffix
     */
    private $suffix;
    /**
     * @var string $lastName
     */
    private $lastName;
    /**
     * @var string $firstName
     */
    private $firstName;
    /**
     * @var string $middleName
     */
    private $middleName;
    /**
     * @var string $title
     */
    private $title;
    /**
     * @var string $password
     */
    private $password;
    /**
     * @var string $url
     */
    private $url;

    /**
     * @var bool
     */
    private $acceptMarketing;

    /**
     * @var ArrayCollection
     */
    private $institutions;
    /**
     * @var ArrayCollection
     */
    private $addresses;
    /**
     * @var string
     */
    private $profileFileProvided;

    /**
     * @var string $icProfileVersion
     */
    private $icProfileVersion;

    /**
     * @var bool $icProfileVersion
     */
    private $digitalAutoapproval;

    /**
     * @var bool
     */
    private $hasInterests;

    /**
     * @var string
     */
    private $interests;

    /**
     * @var Array
     */
    private $profileFile;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->institutions = new ArrayCollection();
        $this->addresses = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getCreateUser(): ?string
    {
        return $this->createUser;
    }

    /**
     * @param string $createUser
     */
    public function setCreateUser(string $createUser): void
    {
        $this->createUser = $createUser;
    }

    /**
     * @return string
     */
    public function getUserId(): ?string
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     */
    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getCpf(): ?string
    {
        return $this->cpf;
    }

    /**
     * @param string $cpf
     */
    public function setCpf(string $cpf): void
    {
        $this->cpf = $cpf;
    }

    /**
     * @return string
     */
    public function getSpecialty(): ?string
    {
        return $this->specialty;
    }

    /**
     * @param string $specialty
     */
    public function setSpecialty(string $specialty): void
    {
        $this->specialty = $specialty;
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
    public function getStage(): ?string
    {
        return $this->stage;
    }

    /**
     * @param string $stage
     */
    public function setStage(string $stage): void
    {
        $this->stage = $stage;
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
    public function getInstitutionId(): ?string
    {
        return $this->institutionId;
    }

    /**
     * @param string $institutionId
     */
    public function setInstitutionId(string $institutionId): void
    {
        $this->institutionId = $institutionId;
    }

    /**
     * @return string
     */
    public function getDepartmentName(): ?string
    {
        return $this->departmentName;
    }

    /**
     * @param string $departmentName
     */
    public function setDepartmentName(string $departmentName): void
    {
        $this->departmentName = $departmentName;
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
    public function setDepartmentId(string $departmentId): void
    {
        $this->departmentId = $departmentId;
    }

    /**
     * @return string
     */
    public function getAmPhone(): ?string
    {
        return $this->amPhone;
    }

    /**
     * @param string $amPhone
     */
    public function setAmPhone(string $amPhone): void
    {
        $this->amPhone = $amPhone;
    }

    /**
     * @return string
     */
    public function getAmEmail(): ?string
    {
        return $this->amEmail;
    }

    /**
     * @param string $amEmail
     */
    public function setAmEmail(string $amEmail): void
    {
        $this->amEmail = $amEmail;
    }

    /**
     * @return string
     */
    public function getAmName(): ?string
    {
        return $this->amName;
    }

    /**
     * @param string $amName
     */
    public function setAmName(string $amName): void
    {
        $this->amName = $amName;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    /**
     * @param string $mobile
     */
    public function setMobile(?string $mobile): void
    {
        $this->mobile = $mobile;
    }

    /**
     * @return string
     */
    public function getMainPhone(): ?string
    {
        return $this->mainPhone;
    }

    /**
     * @param string $mainPhone
     */
    public function setMainPhone(string $mainPhone): void
    {
        $this->mainPhone = $mainPhone;
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
            $country = $country->Text;
        }
        $this->country = $country;
    }


    /**
     * @return string
     */
    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    /**
     * @param string $suffix
     */
    public function setSuffix(string $suffix): void
    {
        $this->suffix = $suffix;
    }

    /**
     * @return string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    /**
     * @param string $middleName
     */
    public function setMiddleName(?string $middleName): void
    {
        $this->middleName = $middleName;
    }

    /**
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return bool
     */
    public function isAcceptMarketing(): bool
    {
        return $this->acceptMarketing;
    }

    /**
     * @param bool $acceptMarketing
     */
    public function setAcceptMarketing(bool $acceptMarketing): void
    {
        $this->acceptMarketing = $acceptMarketing;
    }

    /**
     * @return string
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    /**
     * @return ArrayCollection
     */
    public function getInstitutions(): ArrayCollection
    {
        $iterator = $this->institutions->getIterator();
        $iterator->uasort(function (Institution $a, Institution $b)
        {
            $order = 0;
            if ($a->isPrimary())
            {
                $order = -1;
            } elseif ($b->isPrimary())
            {
                $order = 1;
            } elseif (!$a->isEnabled())
            {
                $order = 1;
            } elseif (!$b->isEnabled())
            {
                $order = -1;
            } else {
                // SonarQube Rule
            }
            return $order;
        });
        $this->institutions = new ArrayCollection(iterator_to_array($iterator));
        return $this->institutions;
    }

    /**
     * @param ArrayCollection $institutions
     */
    public function setInstitutions(ArrayCollection $institutions): void
    {
        $this->institutions = $institutions;
    }

    /**
     * @param Institution $institution
     */
    public function addInstitution(Institution $institution): void
    {
        $this->institutions->add($institution);
    }

    /**
     * update user institution by its id
     * @param Institution $institution
     * @return void
     */
    public function updateInstitution(Institution $institution): void
    {
        /** @var Institution $institution */
        $this->institutions = $this->institutions->map(function (Institution $inst) use ($institution)
        {
            return $inst->getId() == $institution->getId() ? $institution : $inst;
        });
    }

    /**
     * Remove user institution by its id
     * @param $institutionId
     * @return void
     */
    public function removeInstitution($institutionId): void
    {
        /**
         * @var Institution $institution
         */
        foreach ($this->institutions as $institution)
        {
            if ($institution->getId() == $institutionId)
            {
                $this->institutions->removeElement($institution);
                break;
            }
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getAddresses(): ArrayCollection
    {
        if (!$this->addresses)
        {
            $this->addresses = new ArrayCollection();
        }
        $iterator = $this->addresses->getIterator();
        $iterator->uasort(function (Address $a, Address $b)
        {
            if ($a->getType() === 'Disabled')
            {
                return 1;
            }
            if ($b->getType() === 'Disabled')
            {
                return -1;
            }
            return 0;
        });
        $this->addresses = new ArrayCollection(iterator_to_array($iterator));
        return $this->addresses;
    }

    /**
     * @param ArrayCollection $addresses
     */
    public function setAddresses(?ArrayCollection $addresses): void
    {
        $this->addresses = $addresses;
    }

    /**
     * @param Address $address
     */
    public function addAddress(Address $address): void
    {
        if (!$this->addresses)
        {
            $this->addresses = new ArrayCollection();
        }
        $this->addresses->add($address);
    }

    /**
     * update user address by its id
     * @param Address $address
     * @return void
     */
    public function updateAddress(Address $address): void
    {
        /** @var Address $address */
        $this->addresses = $this->addresses->map(function (Address $add) use ($address)
        {
            return $add->getId() == $address->getId() ? $address : $add;
        });
    }

    /**
     * Remove user address by its id
     * @param $addressId
     * @return void
     */
    public function removeAddress($addressId): void
    {
        /**
         * Search for address and remove it
         * @var Address $address
         */
        foreach ($this->addresses as $address)
        {
            if ($address->getId() == $addressId)
            {
                $this->addresses->removeElement($address);
                break;
            }
        }
    }
    /**
     * @return ArrayCollection
     */
    public function getProfileFile(): Array
    {
        return $this->profileFile ? $this->profileFile: [];
    }

    /**
     * @param ArrayCollection
     */
    public function setProfileFile(?Array $profileFile): void
    {
        $this->profileFile = $profileFile;
    }

    /**
     * @param $website
     */
    public function setWebsite($website): void
    {
        $this->url = $website;
    }

    /**
     * @param $vatNumber
     */
    public function setVatNumber($vatNumber): void
    {
        $this->cpf = $vatNumber;
    }

    /**
     * Return user primary institution if has one
     * @return Institution|null
     */
    public function getPrimaryInstitution(): ?Institution
    {
        $primary = $this->institutions->filter(function (Institution $inst)
        {
            return $inst->isPrimary();
        });
        return $primary->count() > 0 ? $primary->first() : null;
    }

    /**
     * @return string|null
     */
    public function getProfileFileProvided(): ?string
    {
        return $this->profileFileProvided;
    }

    /**
     * @param string $profileFileProvided
     */
    public function setProfileFileProvided(string $profileFileProvided): void
    {
        $this->profileFileProvided = $profileFileProvided;
    }

    /**
     * @return string
     */
    public function getIcProfileVersion(): ?string
    {
        return $this->icProfileVersion;
    }

    /**
     * @param string $icProfileVersion
     */
    public function setIcProfileVersion(string $icProfileVersion)
    {
        $this->icProfileVersion = $icProfileVersion;
    }

    /**
     * @return bool
     */
    public function isDigitalAutoapproval(): ?bool
    {
        return $this->digitalAutoapproval;
    }

    /**
     * @param bool $digitalAutoapproval
     */
    public function setDigitalAutoapproval(bool $digitalAutoapproval)
    {
        $this->digitalAutoapproval = $digitalAutoapproval;
    }

    /**
     * @param bool $hasInterests
     */
    public function setHasInterests(bool $hasInterests): void
    {
        $this->hasInterests = $hasInterests;
    }

    /**
     * @return bool
     */
    public function getHasInterests(): ?bool
    {
        return $this->hasInterests;
    }

    /**
     * @param string $interests
     */
    public function setInterests(string $interests): void
    {
        $this->interests = $interests;
    }

    /**
     * @return string
     */
    public function getInterests(): ?string
    {
        return $this->interests;
    }
}
