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
 */
class Register
{
    /**
     * @var integer
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=64 , name="user_id")
     */
    private $userId;

    /**
     * @var $email
     * @ORM\Column(type="string")
     */
    private $email;

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
     */
    private $acceptMarketing;

    /**
     * UserRegister constructor.
     */
    public function __construct()
    {
        $this->token = TokenGenerator::generateToken();
        $this->requestedAt = new \DateTime();
        $this->acceptMarketing = false;
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
}
