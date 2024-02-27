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

/**
 * Class RequestRegister
 * @package UserBundle\Entity
 * @ORM\Table(name="elsic_users")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\AdminRepository")
 */
class Admin
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id" ,type="integer")
     */
    private $id;
    /**
     * @var string
     * @ORM\Column(name="username", type="string" , length=50)
     */
    private $username;

    /**
     * @var string
     * @ORM\Column(name="password", type="string" , length=150)
     */
    private $password;

    /**
     * @var \DateTime
     * @ORM\Column(name="regdate",type="datetime")
     */
    private $registrationDate;

    /**
     * Admin constructor.
     */
    public function __construct()
    {
        $this->registrationDate = new \DateTime();
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
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
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
     * @return \DateTime
     */
    public function getRegistrationDate(): \DateTime
    {
        return $this->registrationDate;
    }

    /**
     * @param \DateTime $registrationDate
     */
    public function setRegistrationDate(\DateTime $registrationDate): void
    {
        $this->registrationDate = $registrationDate;
    }
}
