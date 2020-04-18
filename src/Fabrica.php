<?php declare(strict_types=1);

namespace Fabrica\Fabrica;

use Fabrica\Fabrica\Store\StoreInterface;

class Fabrica
{
	/** @var StoreInterface|null */
	private $store;

	/** @var callable[] */
	private $defined = [];

	public function __construct(StoreInterface $store = null)
	{
		$this->store = $store;
	}

	public function define(string $class, callable $definition)
	{
		$this->defined[$class] = $definition;
	}

	public function create(string $class)
	{
		$attributes = $this->defined[$class]();
		$entity = new $class;

		foreach ($attributes as $attribute => $value) {
			$entity->$attribute = $value;
		}

		if ($this->store) {
			$this->store->save($entity);
		}

		return $entity;
	}
}
