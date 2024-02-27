<?php
/**
 * This file is part of the Inspection Copy.
 * @copyright  Copyright (C) 2019 Elsevier.
 * Created by mobelite.
 * Date: 4/11/18
 * Time: 17:40
 * @author: Mobelite <www.mobelite.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MBComponents\Bundles;

use lib\Config;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\Loader\YamlFileLoader;

/**
 * Class AbstractBundle
 * @package MBComponents\Bundles
 */
abstract class AbstractBundle implements BundleInterface
{
    /**
     * @var
     */
    protected $name;
    /**
     * @var
     */
    protected $path;
    /**
     * @var
     */
    private $namespace;

    /**
     *
     */
    private function parseClassName()
    {
        $pos = strrpos(static::class, '\\');
        $this->namespace = false === $pos ? '' : substr(static::class, 0, $pos);
        if (null === $this->name) {
            $this->name = false === $pos ? static::class : substr(static::class, $pos + 1);
        }
    }

    /**
     * Returns the bundle name (the class short name).
     *
     * @return string The Bundle name
     */
    final public function getName()
    {
        if (null === $this->name) {
            $this->parseClassName();
        }
        return $this->name;
    }

    /**
     * Gets the Bundle directory path.
     *
     * @return string The Bundle absolute path
     */
    public function getPath()
    {
        if (null === $this->path) {
            $reflected = new \ReflectionObject($this);
            $this->path = dirname($reflected->getFileName());
        }
        return $this->path;
    }

    /**
     * Gets the Bundle namespace.
     *
     * @return string The Bundle namespace
     */
    public function getNamespace()
    {
        if (null === $this->namespace) {
            $this->parseClassName();
        }
        return $this->namespace;
    }
}
