<?php declare(strict_types=1);

namespace Fabrica\Test;

use Fabrica\Fabrica;
use Fabrica\Test\Entities\Post;
use Fabrica\Test\Entities\User;

trait TestEntities
{
	/** @var Fabrica */
	private $fabrica;

	private function defineUser(array $data = [])
	{
		$this->fabrica->define(User::class, function () use ($data) {
			return array_merge([
				'firstName' => 'Test',
				'lastName' => 'User',
				'age' => 36,
			], $data);
		});
	}

	private function definePost(array $data = [])
	{
		$this->fabrica->define(Post::class, function () use ($data) {
			return array_merge([
				'title' => 'My first post',
				'body' => 'Something revolutionary',
			], $data);
		});
	}
}
