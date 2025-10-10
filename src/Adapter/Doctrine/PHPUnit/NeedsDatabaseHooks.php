<?php declare(strict_types=1);

namespace Noj\Fabrica\Adapter\Doctrine\PHPUnit;

use Noj\Fabrica\Adapter\Doctrine\SchemaManager;
use Noj\Fabrica\Fabrica;

trait NeedsDatabaseHooks
{
	protected function setUp(): void
	{
		$entityManager = Fabrica::getEntityManager();

		SchemaManager::create($entityManager);
		$entityManager->beginTransaction();
	}

	protected function tearDown(): void
	{
		$entityManager = Fabrica::getEntityManager();
		$entityManager->rollback();
		$entityManager->clear();
	}
}
