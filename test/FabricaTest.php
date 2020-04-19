<?php declare(strict_types=1);

namespace Fabrica\Test;

use Fabrica\Fabrica;
use Fabrica\Test\Entities\User;
use PHPUnit\Framework\TestCase;

class FabricaTest extends TestCase
{
	/** @test */
	public function it_can_define_and_create_a_factory_using_properties()
	{
		$fabrica = new Fabrica();
		$fabrica->define(User::class, function () {
			return [
				'firstName' => 'Test',
				'lastName' => 'User',
			];
		});

		$user = $fabrica->create(User::class);

		self::assertEquals('Test', $user->firstName);
		self::assertEquals('User', $user->lastName);
	}

	/** @test */
	public function it_can_define_and_create_a_factory_using_methods()
	{
		$fabrica = new Fabrica();
		$fabrica->define(User::class, function () {
			return [
				'@setFirstName' => 'Test',
				'@setLastName' => 'User',
			];
		});

		$user = $fabrica->create(User::class);

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
		(new Fabrica())->create(User::class);
	}

	/** @test */
	public function it_can_override_definition_when_creating()
	{
		$fabrica = new Fabrica();
		$fabrica->define(User::class, function () {
			return [
				'firstName' => 'Test',
				'@setLastName' => 'User',
				'age' => 47
			];
		});

		$user = $fabrica->create(User::class, [
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
		$fabrica = new Fabrica();
		$fabrica->define(User::class, function () {
			return [
				'firstName' => 'Test',
				'@setLastName' => 'User',
				'age' => 47
			];
		});

		$users = $fabrica->from(User::class)
			->instances(2)
			->create();

		self::assertCount(2, $users);
		self::assertContainsOnlyInstancesOf(User::class, $users);

		foreach ($users as $user) {
			self::assertEquals('Test', $user->firstName);
			self::assertEquals('User', $user->lastName);
			self::assertSame(47, $user->age);
		}
	}
}
