<?php declare(strict_types=1);

namespace Fabrica\Fabrica\Test\Store;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Fabrica\Fabrica\Fabrica;
use Fabrica\Fabrica\Test\Entities\Post;
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

		$fabrica->create(User::class);

		$this->entityManager->clear();
		$repository = $this->entityManager->getRepository(User::class);
		$users = $repository->findAll();

		self::assertCount(1, $users);
		self::assertEquals('Test', $users[0]->firstName);
		self::assertEquals('User', $users[0]->lastName);
	}

	/** @test */
	public function it_can_create_many_to_one_relation()
	{
		$doctrineStore = new DoctrineStore($this->entityManager);
		$fabrica = new Fabrica($doctrineStore);

		$fabrica->define(User::class, function () {
			return [
				'firstName' => 'Test',
				'lastName' => 'User',
			];
		});

		$fabrica->define(Post::class, function () use ($fabrica) {
			return [
				'title' => 'My first post',
				'body' => 'Something revolutionary',
				'user' => $fabrica->create(User::class)
			];
		});

		$fabrica->create(Post::class);

		$this->entityManager->clear();
		$repository = $this->entityManager->getRepository(Post::class);
		$post = $repository->findOneBy([]);

		self::assertInstanceOf(Post::class, $post);
		self::assertEquals('My first post', $post->title);

		self::assertInstanceOf(User::class, $post->user);
		self::assertEquals('Test', $post->user->getFirstName());
		self::assertEquals('User', $post->user->getLastName());
	}
}
