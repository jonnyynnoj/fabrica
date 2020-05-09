<?php declare(strict_types=1);

namespace Noj\Fabrica\Adapter\Doctrine\PHPUnit;

use Noj\Fabrica\Adapter\Doctrine\SchemaManager;
use Noj\Fabrica\Fabrica;

trait DatabaseFixturesForVersion7AndPrevious
{
	protected function setUp()
	{
		SchemaManager::create(Fabrica::getEntityManager());
	}

	protected function tearDown()
	{
		$entityManager = Fabrica::getEntityManager();

		SchemaManager::truncate($entityManager);
		$entityManager->clear();
	}
}
