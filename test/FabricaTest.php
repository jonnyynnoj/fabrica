<?php declare(strict_types=1);

namespace Noj\Fabrica\Test;

use Noj\Fabrica\Fabrica;
use Noj\Fabrica\FabricaException;
use Noj\Fabrica\Test\Entities\Post;
use Noj\Fabrica\Test\Entities\SuperUser;
use Noj\Fabrica\Test\Entities\User;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FabricaTest extends TestCase
{
	use TestEntities;

	#[Before]
	protected function reset()
	{
		Fabrica::reset();
	}

	#[Test]
	public function it_can_define_and_create_a_factory_using_properties()
	{
		$this->defineUser();

		$user = Fabrica::create(User::class);

		self::assertEquals('Test', $user->firstName);
		self::assertEquals('User', $user->lastName);
	}

	#[Test]
	public function it_can_define_and_create_a_factory_using_methods()
	{
		Fabrica::define(User::class, fn() => [
			'@setFirstName' => 'Test',
			'@setLastName' => 'User',
		]);

		$user = Fabrica::create(User::class);

		self::assertEquals('Test', $user->getFirstName());
		self::assertEquals('User', $user->getLastName());
	}

	#[Test]
	public function it_handles_trying_to_create_undefined_entity()
	{
		$this->expectException(FabricaException::class);
		$this->expectExceptionMessage('No definition found for Noj\Fabrica\Test\Entities\User:default. Did you forget to define it?');

		Fabrica::create(User::class);
	}

	#[Test]
	public function it_can_override_definition_when_creating()
	{
		Fabrica::define(User::class, fn() => [
			'firstName' => 'Test',
			'@setLastName' => 'User',
			'age' => 47
		]);

		$user = Fabrica::create(User::class, fn() => [
			'@setFirstName' => 'Another',
			'lastName' => 'Person',
		]);

		self::assertEquals('Another', $user->firstName);
		self::assertEquals('Person', $user->lastName);
		self::assertSame(47, $user->age);
	}

	#[Test]
	public function it_can_create_multiple()
	{
		$this->defineUser();

		$users = Fabrica::createMany(User::class, 2);

		self::assertCount(2, $users);
		self::assertContainsOnlyInstancesOf(User::class, $users);

		foreach ($users as $user) {
			self::assertEquals('Test', $user->firstName);
			self::assertEquals('User', $user->lastName);
			self::assertSame(36, $user->age);
		}
	}

	#[Test]
	public function it_can_call_setter_for_each_element_of_array()
	{
		$this->definePost();
		Fabrica::define(User::class, fn() => [
			'firstName' => 'Test',
			'@setLastName' => 'User',
			'age' => 47,
			'@addPost*' => Fabrica::createMany(Post::class, 3)
		]);

		$user = Fabrica::create(User::class);

		self::assertCount(3, $user->posts);
		self::assertContainsOnlyInstancesOf(Post::class, $user->posts);

		foreach ($user->posts as $post) {
			self::assertEquals('My first post', $post->title);
			self::assertEquals('Something revolutionary', $post->body);
		}
	}

	#[Test]
	public function it_can_create_relation()
	{
		$this->definePost(fn() => [
			'user' => Fabrica::create(User::class)
		]);

		Fabrica::define(User::class, fn() => [
			'@addPost' => Fabrica::create(Post::class)
		]);

		$user = Fabrica::create(User::class, fn() => [
			'posts.0.title' => 'My new post'
		]);

		self::assertCount(1, $user->posts);
		self::assertInstanceOf(Post::class, $user->posts[0]);
		self::assertSame($user, $user->posts[0]->user);
		self::assertEquals('My new post', $user->posts[0]->title);
	}

	#[Test]
	public function it_can_handle_cyclical_references()
	{
		Fabrica::define(User::class, fn() => [
			'@addPost' => Fabrica::create(Post::class)
		]);

		Fabrica::define(Post::class, fn() => [
			'user' => Fabrica::create(User::class)
		]);

		$user = Fabrica::create(User::class);

		self::assertCount(1, $user->posts);
		self::assertInstanceOf(Post::class, $user->posts[0]);
		self::assertSame($user, $user->posts[0]->user);
	}

	#[Test]
	public function it_can_handle_overridden_cyclical_references()
	{
		$this->defineUser(fn() => [
			'@addPost' => Fabrica::create(Post::class)
		]);

		Fabrica::define(Post::class, fn() => [
			'user' => Fabrica::create(User::class)
		]);

		$post = Fabrica::create(Post::class, fn() => [
			'user.firstName' => 'Overridden'
		]);

		self::assertEquals('Overridden', $post->user->firstName);
		self::assertCount(1, $post->user->posts);
		self::assertSame($post, $post->user->posts[0]);
		self::assertSame($post->user, $post->user->posts[0]->user);
	}

	#[Test]
	public function it_can_set_callback_arguments()
	{
		Fabrica::addDefineArgument('an argument');

		Fabrica::define(User::class, function ($arg) {
			self::assertEquals('an argument', $arg);
			return [];
		});

		Fabrica::create(User::class);
	}

	#[Test]
	public function it_can_fire_on_created_callback()
	{
		$this->definePost()->onCreated(function (Post $post) {
			self::assertEquals('My first post', $post->title);
		});

		Fabrica::create(Post::class);
	}

	#[Test]
	public function it_can_fire_on_created_callback_for_child_entities()
	{
		$this->defineUser(fn() => [
			'@addPost' => Fabrica::create(Post::class)
		]);

		$this->definePost()->onCreated(function (Post $post) {
			self::assertEquals('My first post', $post->title);
		});

		Fabrica::create(User::class);
	}

	#[Test]
	public function it_can_set_property_to_same_as_relation_property()
	{
		$this->defineUser();

		Fabrica::define(Post::class, fn() => [
			'user' => Fabrica::create(User::class),
			'userFirstName' => Fabrica::property('user.firstName')
		]);

		$post = Fabrica::create(Post::class);
		self::assertEquals($post->userFirstName, $post->user->firstName);
	}

	#[Test]
	public function it_can_set_property_to_same_as_overridden_relation_property()
	{
		$this->defineUser(fn() => [
			'@addPost' => Fabrica::create(Post::class)
		]);

		Fabrica::define(Post::class, fn() => [
			'user' => Fabrica::create(User::class),
			'userFirstName' => Fabrica::property('user.firstName')
		]);

		$user = Fabrica::create(User::class, fn() => [
			'firstName' => 'changed'
		]);

		self::assertEquals('changed', $user->firstName);
		self::assertEquals('changed', $user->posts[0]->user->firstName);
		self::assertEquals('changed', $user->posts[0]->userFirstName);
	}

	#[Test]
	public function it_can_create_types_of_entities()
	{
		$this->defineUser();

		Fabrica::define(User::class, fn() => [
			'firstName' => 'Banned',
			'lastName' => 'User',
			'banned' => true
		])->type('banned');

		$user = Fabrica::create(User::class);
		$bannedUser = Fabrica::create(User::class, 'banned');

		self::assertEquals('Test', $user->firstName);
		self::assertEquals('User', $user->lastName);
		self::assertFalse($user->banned);

		self::assertEquals('Banned', $bannedUser->firstName);
		self::assertEquals('User', $bannedUser->lastName);
		self::assertTrue($bannedUser->banned);
	}

	#[Test]
	public function it_can_define_entity_that_extends_another()
	{
		$this->defineUser();

		Fabrica::define(User::class, fn() => ['banned' => true])
			->type('banned')
			->extends(User::class);

		Fabrica::define(User::class, fn() => ['firstName' => 'Banned'])
			->type('banned2')
			->extends(User::class, 'banned');

		$user = Fabrica::create(User::class, 'banned2', fn() => [
			'age' => 28
		]);

		self::assertEquals('Banned', $user->firstName);
		self::assertEquals('User', $user->lastName);
		self::assertEquals(28, $user->age);
		self::assertTrue($user->banned);
	}

	#[Test]
	public function it_can_extend_different_class()
	{
		$this->defineUser();

		Fabrica::define(SuperUser::class, fn() => ['age' => 72])
			->extends(User::class);

		$superUser = Fabrica::create(SuperUser::class, fn() => [
			'firstName' => 'Super'
		]);

		self::assertEquals('Super', $superUser->firstName);
		self::assertEquals('User', $superUser->lastName);
		self::assertEquals(72, $superUser->age);
	}
}
