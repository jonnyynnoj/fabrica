<?php declare(strict_types=1);

namespace Fabrica\Test;

use Fabrica\Fabrica;
use Fabrica\Test\Entities\Post;
use Fabrica\Test\Entities\User;
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
	 * @expectedException Fabrica\FabricaException
	 * @expectedExceptionMessage No definition found for Fabrica\Test\Entities\User
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

		$user = Fabrica::create(User::class, [
			'@setFirstName' => 'Another',
			'lastName' => 'Person',
		]);

		self::assertEquals($user->firstName, 'Another');
		self::assertEquals($user->lastName, 'Person');
		self::assertSame($user->age, 47);
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

	/**
	 * @test
	 * @expectedException \Fabrica\FabricaException
	 * @expectedExceptionMessage Method invalidMethod does not exist on Fabrica\Test\Entities\User
	 */
	public function it_throws_exception_if_method_invalid()
	{
		Fabrica::define(User::class, function () {
			return ['@invalidMethod' => 'Test'];
		});

		Fabrica::create(User::class);
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
}
