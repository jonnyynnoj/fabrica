<?php declare(strict_types=1);

namespace Fabrica\Store;

interface StoreInterface
{
	public function save($entity);
}
