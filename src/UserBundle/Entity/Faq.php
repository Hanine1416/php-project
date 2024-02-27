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
use MBComponents\Helpers\TokenGenerator;

/**
 * Class Faq
 * @package UserBundle\Entity
 * @ORM\Table(name="faq")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\FaqRepository")
 */
class Faq
{
    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="string", length=64 , name="token",unique=true)
     */
    private $token;

    /**
     * @var string
     * @ORM\Column(name="question",type="text")
     */
    private $question;

    /**
     * @var string
     * @ORM\Column(name="answer", type="text")
     */
    private $answer;

    /**
     * @var int
     * @ORM\Column(name="faqOrder" ,type="integer")
     */
    private $order;

    /**
     * @var string
     * @ORM\Column(name="language", type="string",length=10)
     */
    private $language;

    public function __construct()
    {
        $this->token = TokenGenerator::generateToken();
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
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getQuestion(): string
    {
        return $this->question;
    }

    /**
     * @param string $question
     */
    public function setQuestion(string $question): void
    {
        $this->question = $question;
    }

    /**
     * @return string
     */
    public function getAnswer(): string
    {
        return $this->answer;
    }

    /**
     * @param string $answer
     */
    public function setAnswer(string $answer): void
    {
        $this->answer = $answer;
    }

    /**
     * @return string
     */
    public function getLanguage(): string
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
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * @param int $order
     */
    public function setOrder(int $order): void
    {
        $this->order = $order;
    }
}
