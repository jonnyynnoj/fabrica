<?php declare(strict_types=1);

namespace Noj\Fabrica\Test;

use Noj\Fabrica\Fabrica;
use Noj\Fabrica\Test\Entities\Post;
use Noj\Fabrica\Test\Entities\User;
use PHPUnit\Framework\TestCase;

class FabricaTest extends TestCase
{
	use TestEntities;

	protected function setUp()
	{
		Fabrica::init();
	}

	/** @test */
	public function it_can_define_and_create_a_factory_using_properties()
	{
		$this->defineUser();

		$user = Fabrica::create(User::class);

		self::assertEquals('Test', $user->firstName);
		self::assertEquals('User', $user->lastName);
	}

	/** @test */
	public function it_can_define_and_create_a_factory_using_methods()
	{
		Fabrica::define(User::class, function () {
			return [
				'@setFirstName' => 'Test',
				'@setLastName' => 'User',
			];
		});

		$user = Fabrica::create(User::class);

		self::assertEquals('Test', $user->getFirstName());
		self::assertEquals('User', $user->getLastName());
	}

	/**
	 * @test
	 * @expectedException Noj\Fabrica\FabricaException
	 * @expectedExceptionMessage No definition found for Noj\Fabrica\Test\Entities\User
	 */
	public function it_handles_trying_to_create_undefined_entity()
	{
		Fabrica::create(User::class);
	}

	/** @test */
	public function it_can_override_definition_when_creating()
	{
		Fabrica::define(User::class, function () {
			return [
				'firstName' => 'Test',
				'@setLastName' => 'User',
				'age' => 47
			];
		});

		$user = Fabrica::create(User::class, function () {
			return [
				'@setFirstName' => 'Another',
				'lastName' => 'Person',
			];
		});

		self::assertEquals('Another', $user->firstName);
		self::assertEquals('Person', $user->lastName);
		self::assertSame(47, $user->age);
	}

	/** @test */
	public function it_can_create_multiple()
	{
		$this->defineUser();

		$users = Fabrica::of(User::class, 2)->create();

		self::assertCount(2, $users);
		self::assertContainsOnlyInstancesOf(User::class, $users);

		foreach ($users as $user) {
			self::assertEquals('Test', $user->firstName);
			self::assertEquals('User', $user->lastName);
			self::assertSame(36, $user->age);
		}
	}

	/** @test */
	public function it_can_call_setter_for_each_element_of_array()
	{
		$this->definePost();
		Fabrica::define(User::class, function () {
			return [
				'firstName' => 'Test',
				'@setLastName' => 'User',
				'age' => 47,
				'@addPost*' => Fabrica::of(Post::class, 3)->create()
			];
		});

		$user = Fabrica::create(User::class);

		self::assertCount(3, $user->posts);
		self::assertContainsOnlyInstancesOf(Post::class, $user->posts);

		foreach ($user->posts as $post) {
			self::assertEquals('My first post', $post->title);
			self::assertEquals('Something revolutionary', $post->body);
		}
	}

	/** @test */
	public function it_can_create_relation()
	{
		$this->definePost(function () {
			return ['user' => Fabrica::create(User::class)];
		});

		Fabrica::define(User::class, function () {
			return [
				'@addPost' => Fabrica::create(Post::class)
			];
		});

		$user = Fabrica::create(User::class, function () {
			return [
				'posts.0.title' => 'My new post'
			];
		});

		self::assertCount(1, $user->posts);
		self::assertInstanceOf(Post::class, $user->posts[0]);
		self::assertSame($user, $user->posts[0]->user);
		self::assertEquals('My new post', $user->posts[0]->title);
	}

	/** @test */
	public function it_can_handle_cyclical_references()
	{
		Fabrica::define(User::class, function () {
			return [
				'@addPost' => Fabrica::create(Post::class)
			];
		});

		Fabrica::define(Post::class, function () {
			return [
				'user' => Fabrica::create(User::class)
			];
		});

		$user = Fabrica::create(User::class);

		self::assertCount(1, $user->posts);
		self::assertInstanceOf(Post::class, $user->posts[0]);
		self::assertSame($user, $user->posts[0]->user);
	}

	/** @test */
	public function it_can_handle_overridden_cyclical_references()
	{
		$this->defineUser(function () {
			return [
				'@addPost' => Fabrica::create(Post::class)
			];
		});

		Fabrica::define(Post::class, function () {
			return [
				'user' => Fabrica::create(User::class)
			];
		});

		$post = Fabrica::create(Post::class, function () {
			return [
				'user' => Fabrica::create(User::class, function () {
					return [
						'firstName' => 'Overridden'
					];
				})
			];
		});

		self::assertEquals('Overridden', $post->user->firstName);
		self::assertCount(1, $post->user->posts);
		self::assertSame($post, $post->user->posts[0]);
		self::assertSame($post->user, $post->user->posts[0]->user);
	}

	/** @test */
	public function it_can_set_callback_arguments()
	{
		Fabrica::addDefineArgument('an argument');

		Fabrica::define(User::class, function ($arg) {
			self::assertEquals('an argument', $arg);
			return [];
		});

		Fabrica::create(User::class);
	}
}
