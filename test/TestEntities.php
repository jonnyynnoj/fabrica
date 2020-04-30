<?php declare(strict_types=1);

namespace Noj\Fabrica\Test;

use Noj\Fabrica\Fabrica;
use Noj\Fabrica\Test\Entities\Address;
use Noj\Fabrica\Test\Entities\Post;
use Noj\Fabrica\Test\Entities\User;

trait TestEntities
{
	private function defineUser(callable $definition = null)
	{
		Fabrica::define(User::class, function () use ($definition) {
			return array_merge([
				'firstName' => 'Test',
				'lastName' => 'User',
				'age' => 36,
			], $definition ? $definition() : []);
		});

		Fabrica::define(Address::class, function () {
			return [
				'street' => '1 Some Street',
				'city' => 'Test Street',
				'country' => 'Somewhere',
				'postCode' => 'AB12 3CD',
			];
		});
	}

	private function definePost(callable $definition = null)
	{
		return Fabrica::define(Post::class, function () use ($definition) {
			return array_merge([
				'title' => 'My first post',
				'body' => 'Something revolutionary',
			], $definition ? $definition() : []);
		});
	}
}
