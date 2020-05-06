<?php declare(strict_types=1);

namespace Noj\Fabrica\Store;

interface StoreInterface
{
	public function save(array $results);
}
