<?php declare(strict_types=1);

namespace Noj\Fabrica\Adapter\Doctrine\PHPUnit;

use Doctrine\Common\Util\Debug;
use Doctrine\ORM\EntityRepository;
use Noj\Fabrica\Fabrica;
use PHPUnit\Framework\Assert;

trait DatabaseAssertions
{
	protected static function assertDatabaseContainsEntity(string $class, array $criteria = []): ?object
	{
		$entity = self::findDatabaseEntity($class, $criteria);
		Assert::assertNotNull($entity, self::doesntContainMessage($class, $criteria));
		return $entity;
	}

	protected static function assertDatabaseDoesNotContainEntity(string $class, array $criteria = []): void
	{
		Assert::assertNull(self::findDatabaseEntity($class, $criteria));
	}

	protected static function assertDatabaseContainsEntities(string $class, int $amount, array $criteria = []): void
	{
		Assert::assertCount(
			$amount,
			self::getRepository($class)->findBy($criteria),
			self::doesntContainMessage($class, $criteria)
		);
	}

	protected static function assertDatabaseContainsExactlyOneEntity(string $class, array $criteria = []): void
	{
		self::assertDatabaseContainsEntities($class, 1, $criteria);
	}

	protected static function findDatabaseEntity(string $class, array $criteria = []): ?object
	{
		return self::getRepository($class)->findOneBy($criteria);
	}

	protected static function findAll($entity): array
	{
		return self::getRepository($entity)->findAll();
	}

	protected static function getRepository(string $class): EntityRepository
	{
		$entityManager = Fabrica::getEntityManager();
		$entityManager->clear();
		return $entityManager->getRepository($class);
	}

	private static function doesntContainMessage(string $class, array $criteria): string
	{
		return "Database doesn't contain a {$class} with criteria:\n" .
			Debug::dump($criteria, 2, true, false);
	}
}
