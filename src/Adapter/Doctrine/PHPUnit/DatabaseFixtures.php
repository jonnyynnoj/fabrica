<?php declare(strict_types=1);

namespace Noj\Fabrica\Adapter\Doctrine\PHPUnit;

use Noj\Fabrica\Adapter\Doctrine\SchemaManager;
use Noj\Fabrica\Fabrica;

trait DatabaseFixtures
{
	use DatabaseAssertions;

	/** @before */
	protected function create()
	{
		SchemaManager::create(Fabrica::getEntityManager());
	}

	/** @after */
	protected function truncate()
	{
		$entityManager = Fabrica::getEntityManager();

		SchemaManager::truncate($entityManager);
		$entityManager->clear();
	}
}
