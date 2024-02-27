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

use Doctrine\ORM\Mapping as ORM;
use MBComponents\Helpers\TokenGenerator;

/**
 * Class User
 * @package UserBundle\Entity
 * @ORM\Entity(repositoryClass="UserBundle\Repository\ResetPasswordRequestRepository")
 * @ORM\Table(name="reset_password_request")
 */
class ResetPasswordRequest
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
     * @ORM\Column(type="boolean" , name="enabled")
     */
    private $enabled;

    /**
     * @var string
     * @ORM\Column(type="string", name="email")
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(type="string", name="user_identifier")
     */
    private $userIdentifier;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="redirect_profile")
     */
    private $redirectProfile;

    /**
     * ResetPasswordRequest constructor.
     * @param $email
     */
    public function __construct($email)
    {
        $this->token = TokenGenerator::generateToken();
        $this->requestedAt = new \DateTime();
        $this->enabled = true;
        $this->email = $email;
        $this->redirectProfile = false;
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
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token)
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
    public function setRequestedAt(\DateTime $requestedAt)
    {
        $this->requestedAt = $requestedAt;
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
    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }

    /**
     * @param string $userIdentifier
     */
    public function setUserIdentifier(string $userIdentifier)
    {
        $this->userIdentifier = $userIdentifier;
    }

    /**
     * @return bool
     */
    public function isRedirectProfile(): bool
    {
        return $this->redirectProfile;
    }

    /**
     * @param bool $redirectProfile
     */
    public function setRedirectProfile(bool $redirectProfile)
    {
        $this->redirectProfile = $redirectProfile;
    }
}
