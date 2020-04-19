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

	/** @var Fabrica */
	private $fabrica;

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

		$doctrineStore = new DoctrineStore($this->entityManager);
		$this->fabrica = new Fabrica($doctrineStore);
	}

	/** @test */
	public function it_saves_entity_to_database_on_creation()
	{
		$this->fabrica->define(User::class, function () {
			return [
				'firstName' => 'Test',
				'lastName' => 'User',
			];
		});

		$this->fabrica->create(User::class);

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
		$this->fabrica->define(User::class, function () {
			return [
				'firstName' => 'Test',
				'lastName' => 'User',
			];
		});

		$this->fabrica->define(Post::class, function () {
			return [
				'title' => 'My first post',
				'body' => 'Something revolutionary',
				'user' => $this->fabrica->create(User::class)
			];
		});

		$this->fabrica->create(Post::class);

		$this->entityManager->clear();
		$repository = $this->entityManager->getRepository(Post::class);
		$post = $repository->findOneBy([]);

		self::assertInstanceOf(Post::class, $post);
		self::assertEquals('My first post', $post->title);

		self::assertInstanceOf(User::class, $post->user);
		self::assertEquals('Test', $post->user->getFirstName());
		self::assertEquals('User', $post->user->getLastName());
	}

	/** @test */
	public function it_can_create_one_to_many_relation()
	{
		$this->fabrica->define(User::class, function () {
			return [
				'firstName' => 'Test',
				'lastName' => 'User',
				'@addPost' => $this->fabrica->create(Post::class)
			];
		});

		$this->fabrica->define(Post::class, function () {
			return [
				'title' => 'My first post',
				'body' => 'Something revolutionary',
			];
		});

		$this->fabrica->create(User::class);

		$this->entityManager->clear();

		$repository = $this->entityManager->getRepository(User::class);
		$user = $repository->findOneBy([]);

		self::assertInstanceOf(User::class, $user);
		self::assertCount(1, $user->posts);
		self::assertInstanceOf(Post::class, $user->posts[0]);
	}
}
