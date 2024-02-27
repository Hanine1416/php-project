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

use DOctrine\ORM\Mapping as ORM;

/**
 * Class WSDebug
 * @package MainBundle\Entity
 *  * @ORM\Table(name="web_services_debug")
 * @ORM\Entity()
 */
class WSDebug
{
    /**
     * @var
     * @ORM\Id
     * @ORM\Column(name="id" ,type="string")
     */
    private $identifier;

    /**
     * @var string $query
     * @ORM\Column(type="text")
     */
    private $response = [];

    /**
     * @var array $query
     * @ORM\Column(type="array")
     */
    private $query = [];

    /**
     * WSDebug constructor.
     * @param string $identifier
     */
    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return mixed
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getResponse(): string
    {
        return $this->response;
    }

    /**
     * @return array
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    /**
     * @param string $response
     */
    public function setResponse(string $response)
    {
        $this->response = $response;
    }

    /**
     * @param array $query
     */
    public function setQuery(array $query)
    {
        $this->query = $query;
    }
}
