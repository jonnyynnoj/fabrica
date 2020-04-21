<?php declare(strict_types=1);

namespace Fabrica\Test\Store;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Fabrica\Fabrica;
use Fabrica\Test\Entities\Post;
use Fabrica\Test\Entities\User;
use Fabrica\Store\DoctrineStore;
use Fabrica\Test\TestEntities;
use PHPUnit\Framework\TestCase;

class DoctrineStoreTest extends TestCase
{
	use TestEntities;

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

		$doctrineStore = new DoctrineStore($this->entityManager);
		Fabrica::init($doctrineStore);
	}

	/** @test */
	public function it_saves_entity_to_database_on_creation()
	{
		$this->defineUser();

		Fabrica::create(User::class);

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
		$this->defineUser();
		$this->definePost(function () {
			return ['user' => Fabrica::create(User::class)];
		});

		Fabrica::create(Post::class);

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
		$this->definePost();
		$this->defineUser(function () {
			return ['@addPost' => Fabrica::create(Post::class)];
		});

		Fabrica::create(User::class);

		$this->entityManager->clear();
		$repository = $this->entityManager->getRepository(User::class);
		$user = $repository->findOneBy([]);

		self::assertInstanceOf(User::class, $user);
		self::assertCount(1, $user->posts);
		self::assertInstanceOf(Post::class, $user->posts[0]);
	}

	/** @test */
	public function it_can_create_multiple_relations()
	{
		$this->defineUser(function () {
			return ['@addPost*' => Fabrica::of(Post::class, 3)->create()];
		});

		$this->definePost(function () {
			return ['user' => Fabrica::create(User::class)];
		});

		Fabrica::create(User::class);

		$this->entityManager->clear();
		$repository = $this->entityManager->getRepository(User::class);
		$users = $repository->findAll();

		self::assertCount(1, $users);
		self::assertCount(3, $users[0]->posts);
		self::assertContainsOnlyInstancesOf(Post::class, $users[0]->posts);

		foreach ($users[0]->posts as $post) {
			self::assertSame($users[0], $post->user);
		}
	}
}
