<?php

require 'config.php';
require 'preConfig.php';

$autoloader = require "vendor/autoload.php";

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

AnnotationRegistry::registerAutoloadNamespace(
    'JMS\Serializer\Annotation',
    __DIR__ . "/vendor/jms/serializer/src"
);

/** init Doctrine EntityManager */
$doctrineConfig = new \MBComponents\Doctrine\DoctrineConfig(true);
$sfEntityManager = $doctrineConfig->getEntityManager();
$doctrineStack = $doctrineConfig->getStack();

return ConsoleRunner::createHelperSet($sfEntityManager);
