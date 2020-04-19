<?php declare(strict_types=1);

namespace Fabrica\Fabrica\Test;

use Fabrica\Fabrica\Fabrica;
use Fabrica\Fabrica\Test\Entities\User;
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
	 * @expectedException Fabrica\Fabrica\FabricaException
	 * @expectedExceptionMessage No definition found for Fabrica\Fabrica\Test\Entities\User
	 */
	public function it_handles_trying_to_create_undefined_entity()
	{
		(new Fabrica())->create(User::class);
	}
}
