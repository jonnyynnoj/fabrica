<?php declare(strict_types=1);

namespace Noj\Fabrica\Adapter\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;

class SchemaManager
{
	private static $created = false;

	public static function create(EntityManager $entityManager)
	{
		if (!self::$created) {
			$metaData = $entityManager->getMetadataFactory()->getAllMetadata();
			(new SchemaTool($entityManager))->updateSchema($metaData);
			self::$created = true;
		}
	}
}
