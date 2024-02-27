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
use MBComponents\Helpers\Encryption;
use MBComponents\Helpers\TokenGenerator;

/**
 * Class RequestRegister
 * @package UserBundle\Entity
 * @ORM\Table(name="create_account")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\RequestRegisterRepository")
 */
class RequestRegister
{
    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var $email
     * @ORM\Column(type="string")
     */
    private $email;

    /**
     * @var $title
     * @ORM\Column(type="string", nullable=true)
     */
    private $title;

    /**
     * @var $firstName
     * @ORM\Column(type="string")
     */
    private $firstName;

    /**
     * @var $middleName
     * @ORM\Column(type="string", nullable=true)
     */
    private $middleName;

    /**
     * @var $url
     * @ORM\Column(type="string", nullable=true)
     */
    private $url;

    /**
     * @var $lastName
     * @ORM\Column(type="string")
     */
    private $lastName;

    /**
     * @var $password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @var string
     * @ORM\Column(type="string", length=64 , name="token")
     */
    private $token;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime" , name="requested_at")
     */
    private $requestedAt;

    /**
     * @var bool
     * @ORM\Column(type="boolean" , name="accept_marketing")
     */
    private $acceptMarketing;

    /**
     * @var string
     * @ORM\Column(type="string" , name="my_ic_guide")
     */
    private $myIcGuide;

    /**
     * UserRegister constructor.
     */
    public function __construct()
    {
        $this->token = TokenGenerator::generateToken();
        $this->requestedAt = new \DateTime();
        $this->acceptMarketing = false;
        $this->myIcGuide = "first login";
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
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return  $this->password?Encryption::decrypt($this->password):'';
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->password = Encryption::encrypt($password);
    }

    /**
     * @return string
     */
    public function getToken(): ?string
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
     * @return \DateTime
     */
    public function getRequestedAt(): \DateTime
    {
        return $this->requestedAt;
    }

    /**
     * @param \DateTime $requestedAt
     */
    public function setRequestedAt(\DateTime $requestedAt): void
    {
        $this->requestedAt = $requestedAt;
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
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getMiddleName()
    {
        return $this->middleName;
    }

    /**
     * @param mixed $middleName
     */
    public function setMiddleName($middleName): void
    {
        $this->middleName = $middleName;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url): void
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getMyIcGuide(): string
    {
        return $this->myIcGuide;
    }

    /**
     * @param string $myIcGuide
     */
    public function setMyIcGuide(string $myIcGuide): void
    {
        $this->myIcGuide = $myIcGuide;
    }


}
