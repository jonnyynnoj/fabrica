<?php declare(strict_types=1);

namespace Fabrica\Fabrica\Test;

use Fabrica\Fabrica\Fabrica;
use Fabrica\Fabrica\Test\Entities\User;
use PHPUnit\Framework\TestCase;

class FabricaTest extends TestCase
{
	/** @test */
	public function i_can_define_and_create_a_factory()
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
}
