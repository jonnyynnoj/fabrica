<?php declare(strict_types=1);

namespace Noj\Fabrica\Test\Adapter\Doctrine;

use Noj\Fabrica\Adapter\Doctrine\DoctrineStore;
use Noj\Fabrica\Adapter\Doctrine\EntityManagerFactory;
use Noj\Fabrica\Adapter\Doctrine\PHPUnit\NeedsDatabase;
use Noj\Fabrica\Fabrica;
use Noj\Fabrica\Test\Entities\Address;
use Noj\Fabrica\Test\Entities\Post;
use Noj\Fabrica\Test\Entities\User;
use Noj\Fabrica\Test\TestEntities;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 */
class DoctrineStoreTest extends TestCase
{
	use NeedsDatabase, TestEntities {
		setUp as createSchema;
	}

	/** @before */
	protected function initDoctrine()
	{
		$entityManager = EntityManagerFactory::createSQLiteInMemory([__DIR__ . '/../../Entities']);
		Fabrica::setStore(new DoctrineStore($entityManager));

		$this->createSchema();
	}

	/** @test */
	public function it_saves_entity_to_database_on_creation()
	{
		$this->defineUser();

		Fabrica::create(User::class);

		self::assertDatabaseContainsEntity(User::class, [
			'firstName' => 'Test',
			'lastName' => 'User',
		]);
	}

	/** @test */
	public function it_can_create_many_to_one_relation()
	{
		$this->defineUser();

		$this->definePost(fn() => [
			'user' => Fabrica::create(User::class)
		]);

		Fabrica::create(Post::class);

		$user = self::assertDatabaseContainsEntity(User::class, [
			'firstName' => 'Test',
			'lastName' => 'User'
		]);

		self::assertDatabaseContainsEntity(Post::class, [
			'user' => $user,
			'title' => 'My first post'
		]);
	}

	/** @test */
	public function it_can_create_one_to_many_relation()
	{
		$this->definePost(fn() => [
			'user' => Fabrica::create(User::class)
		]);

		$this->defineUser(fn() => [
			'@addPost' => Fabrica::create(Post::class)
		]);

		$user = Fabrica::create(User::class);

		self::assertDatabaseContainsExactlyOneEntity(Post::class, [
			'user' => $user,
			'title' => 'My first post'
		]);
	}

	/** @test */
	public function it_can_create_multiple_relations()
	{
		$this->defineUser(fn() => [
			'@addPost*' => Fabrica::createMany(Post::class, 3)
		]);

		$this->definePost(fn() => [
			'user' => Fabrica::create(User::class)
		]);

		$user = Fabrica::create(User::class);

		self::assertDatabaseContainsEntities(Post::class, 3, ['user' => $user]);
	}

	/** @test */
	public function it_can_handle_embeddable()
	{
		$this->defineUser(fn() => [
			'address' => Fabrica::create(Address::class)
		]);

		Fabrica::define(Address::class, fn() => [
			'street' => '1 Test Street',
			'city' => 'Test City',
			'country' => 'Test'
		]);

		Fabrica::create(User::class);

		$user = self::assertDatabaseContainsEntity(User::class, ['firstName' => 'Test']);
		self::assertInstanceOf(Address::class, $user->address);
		self::assertEquals('1 Test Street', $user->address->street);
	}
}
