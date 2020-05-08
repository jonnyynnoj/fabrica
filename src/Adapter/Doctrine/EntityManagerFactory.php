<?php declare(strict_types=1);

namespace Noj\Fabrica\Adapter\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

class EntityManagerFactory
{
	public static function create(array $connection, array $entityPaths): EntityManager
	{
		$config = Setup::createAnnotationMetadataConfiguration($entityPaths, true);
		return EntityManager::create($connection, $config);
	}

	public static function createSQLiteInMemory(array $entityPaths): EntityManager
	{
		return self::create([
			'driver' => 'pdo_sqlite',
			'memory' => true
		], $entityPaths);
	}
}
