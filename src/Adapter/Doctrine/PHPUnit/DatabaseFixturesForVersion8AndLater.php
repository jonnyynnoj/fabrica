<?php declare(strict_types=1);

namespace Noj\Fabrica\Adapter\Doctrine\PHPUnit;

use Noj\Fabrica\Adapter\Doctrine\SchemaManager;
use Noj\Fabrica\Fabrica;

trait DatabaseFixturesForVersion8AndLater
{
	protected function setUp(): void
	{
		SchemaManager::create(Fabrica::getEntityManager());
	}

	protected function tearDown(): void
	{
		$entityManager = Fabrica::getEntityManager();

		SchemaManager::truncate($entityManager);
		$entityManager->clear();
	}
}
