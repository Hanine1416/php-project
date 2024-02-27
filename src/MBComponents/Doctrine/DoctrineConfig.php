<?php

namespace MBComponents\Doctrine;

use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use lib\Config;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Created by PhpStorm.
 * User: mobelite
 * Date: 26/03/2018
 * Time: 12:30
 * Class Config
 * @package MBComponents\Doctrine
 */
class DoctrineConfig
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager = null;
    /** @var \Doctrine\DBAL\Logging\DebugStack|null $stack */
    private $stack = null;

    /**
     * DoctrineConfig constructor.
     * @param bool $devMode
     * @throws \Doctrine\ORM\ORMException
     */
    public function __construct($devMode = true)
    {
        /** Create cache directory */
        $cacheDir = CACHE_DIRECTORY . '/doctrine';
        is_dir($cacheDir) ?: mkdir($cacheDir, 0777, true);
        $cache = new \Doctrine\Common\Cache\ArrayCache();

        /**
         * configuration
         * @var Configuration $config
         */
        $config = Setup::createAnnotationMetadataConfiguration(
            DoctrineConfig::getPaths(),
            $devMode,
            $cacheDir,
            $cache,
            false
        );
        $config->setMetadataCacheImpl($cache);
        $config->setQueryCacheImpl($cache);
        $config->setProxyDir($cacheDir);
        $config->setProxyNamespace('Proxies');  

        /** Configure the database connection  */
        $connectionOptions = [
            'driver' => 'pdo_mysql',
            'user' => Config::read('db.user'),
            'password' => Config::read('db.password'),
            'host' => Config::read('db.host'),
            'dbname' => Config::read('db.basename'),
            'port' => Config::read('db.port')
        ];
        $this->entityManager = EntityManager::create($connectionOptions, $config);

        $connection = $this->entityManager->getConnection();
        $this->stack = new \Doctrine\DBAL\Logging\DebugStack();
        $connection->getConfiguration()->setSQLLogger($this->stack);
    }

    /**
     * Return doctrine entity manager
     * @return EntityManager
     */
    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * Get the path for every entity
     * @return array
     */
    public function getPaths(): array
    {
        $paths = [];
        $finder = new Finder();
        /** @var SplFileInfo $file */
        foreach ($finder->in(__DIR__ . '/../../*Bundle/Entity') as $file) {
            if (!in_array($file->getPath(), $paths)) {
                $paths[] = $file->getPath();
            }
        }

        return $paths;
    }

    /**
     * @return \Doctrine\DBAL\Logging\DebugStack|null
     */
    public function getStack(): ?DebugStack
    {
        return $this->stack;
    }
}
