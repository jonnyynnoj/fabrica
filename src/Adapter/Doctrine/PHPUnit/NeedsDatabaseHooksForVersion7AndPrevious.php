<?php declare(strict_types=1);

namespace Noj\Fabrica\Adapter\Doctrine\PHPUnit;

use Noj\Fabrica\Adapter\Doctrine\SchemaManager;
use Noj\Fabrica\Fabrica;

trait NeedsDatabaseHooksForVersion7AndPrevious
{
	protected function setUp()
	{
		$entityManager = Fabrica::getEntityManager();

		SchemaManager::create($entityManager);
		$entityManager->beginTransaction();
	}

	protected function tearDown()
	{
		$entityManager = Fabrica::getEntityManager();
		$entityManager->rollback();
		$entityManager->clear();
	}
}
