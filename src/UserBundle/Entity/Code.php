<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 08/12/2021
 * Time: 11:39
 */

namespace UserBundle\Entity;

use DOctrine\ORM\Mapping as ORM;

/**
 * Class Code
 * @package UserBundle\Code
 * @ORM\Table(name="code")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\CodeRepository")
 */
class Code
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
     * @var int
     * @ORM\Column(type="integer")
     */
    private $code;

    /**
     * Code constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->code = random_int(100000, 999999);
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
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode(int $code): void
    {
        $this->code = $code;
    }



}
