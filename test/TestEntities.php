<?php declare(strict_types=1);

namespace Fabrica\Test;

use Fabrica\Fabrica;
use Fabrica\Test\Entities\Post;
use Fabrica\Test\Entities\User;

trait TestEntities
{
	/** @var Fabrica */
	private $fabrica;

	private function defineUser(callable $definition = null)
	{
		$this->fabrica->define(User::class, function () use ($definition) {
			return array_merge([
				'firstName' => 'Test',
				'lastName' => 'User',
				'age' => 36,
			], $definition ? $definition() : []);
		});
	}

	private function definePost(callable $definition = null)
	{
		$this->fabrica->define(Post::class, function () use ($definition) {
			return array_merge([
				'title' => 'My first post',
				'body' => 'Something revolutionary',
			], $definition ? $definition() : []);
		});
	}
}
