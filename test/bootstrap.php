<?php declare(strict_types=1);

use Noj\Fabrica\Fabrica;
use Noj\Fabrica\Adapter\Doctrine\DoctrineStore;
use Noj\Fabrica\Adapter\Doctrine\EntityManagerFactory;

require_once __DIR__ . '/../vendor/autoload.php';

$entityManager = EntityManagerFactory::createSQLiteInMemory([__DIR__ . '/entities']);
Fabrica::setStore(new DoctrineStore($entityManager));
