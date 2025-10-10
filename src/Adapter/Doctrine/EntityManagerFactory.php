<?php declare(strict_types=1);

namespace Noj\Fabrica\Adapter\Doctrine;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;

class EntityManagerFactory
{
	public static function create(array $entityPaths, array $params): EntityManager
	{
		$config = ORMSetup::createAttributeMetadataConfiguration($entityPaths, true);
		$connection = DriverManager::getConnection($params, $config);
		return new EntityManager($connection, $config);
	}

	public static function createSQLiteInMemory(array $entityPaths): EntityManager
	{
		return self::create($entityPaths, [
			'driver' => 'pdo_sqlite',
			'memory' => true
		]);
	}
}
