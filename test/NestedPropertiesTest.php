<?php declare(strict_types=1);

namespace Fabrica\Test;

use Fabrica\Fabrica;
use Fabrica\Test\Entities\Account;
use Fabrica\Test\Entities\Address;
use Fabrica\Test\Entities\User;
use PHPUnit\Framework\TestCase;

class NestedPropertiesTest extends TestCase
{
	use TestEntities;

	/** @test */
	public function it_can_override_nested_properties()
	{
		$this->defineUser();

		$user = Fabrica::create(User::class, [
			'firstName' => 'Random',
			'account.@getAddress.street' => 'A different street',
			'account.address.@setCity' => 'London',
		]);

		self::assertEquals('Random', $user->firstName);
		self::assertEquals('A different street', $user->account->address->street);
		self::assertEquals('London', $user->account->address->city);
	}

	/**
	 * @test
	 * @expectedException \Fabrica\FabricaException
	 * @dataProvider propertyDoesntExistProvider
	 */
	public function it_throws_exception_if_nested_property_doesnt_exist($key, $entity)
	{
		$this->defineUser();

		$this->expectExceptionMessage("Nested property foo does not exist on $entity");

		Fabrica::create(User::class, [
			$key => 'This should fail',
		]);
	}

	/**
	 * @test
	 * @expectedException \Fabrica\FabricaException
	 * @dataProvider methodDoesntExistProvider
	 */
	public function it_throws_exception_if_nested_method_doesnt_exist($key, $entity)
	{
		$this->defineUser();

		$this->expectExceptionMessage("Method doesntExist does not exist on $entity");

		Fabrica::create(User::class, [
			$key => 'This should fail',
		]);
	}

	/**
	 * @test
	 * @expectedException \Fabrica\FabricaException
	 * @expectedExceptionMessage Nested property id on Fabrica\Test\Entities\Account is not an object
	 */
	public function it_throws_exception_if_nested_property_is_not_an_object()
	{
		$this->defineUser();

		Fabrica::create(User::class, [
			'account.id.property' => 'This should fail',
		]);
	}

	public function propertyDoesntExistProvider()
	{
		return [
			['foo.property', User::class],
			['account.foo.property', Account::class],
		];
	}

	public function methodDoesntExistProvider()
	{
		return [
			['account.@doesntExist', Account::class],
			['account.address.@doesntExist', Address::class],
		];
	}
}
