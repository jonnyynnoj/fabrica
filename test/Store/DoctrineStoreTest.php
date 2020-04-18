<?php declare(strict_types=1);

namespace Fabrica\Fabrica\Test\Store;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Fabrica\Fabrica\Fabrica;
use Fabrica\Fabrica\Test\Entities\User;
use Fabrica\Fabrica\Store\DoctrineStore;
use PHPUnit\Framework\TestCase;

class DoctrineStoreTest extends TestCase
{
	/** @var EntityManager */
	private $entityManager;

	protected function setUp()
	{
		$db = [
			'driver' => 'pdo_sqlite',
			'memory' => true
		];

		$config = Setup::createAnnotationMetadataConfiguration([__DIR__ . '/../Entities'], true);
		$this->entityManager = EntityManager::create($db, $config);

		$schemaTool = new SchemaTool($this->entityManager);
		$metaData = $this->entityManager->getMetadataFactory()->getAllMetadata();
		$schemaTool->createSchema($metaData);
	}

	/** @test */
	public function it_saves_entity_to_database_on_creation()
	{
		$doctrineStore = new DoctrineStore($this->entityManager);
		$fabrica = new Fabrica($doctrineStore);

		$fabrica->define(User::class, function () {
			return [
				'firstName' => 'Test',
				'lastName' => 'User',
			];
		});

		$user = $fabrica->create(User::class);

		$repository = $this->entityManager->getRepository(User::class);
		$users = $repository->findAll();
		self::assertCount(1, $users);
		self::assertEquals('Test', $users[0]->firstName);
		self::assertEquals('User', $users[0]->lastName);
		self::assertEquals($user, $users[0]);
	}
}
