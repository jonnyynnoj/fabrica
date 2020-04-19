<?php declare(strict_types=1);

namespace Fabrica;

use Fabrica\Store\StoreInterface;

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

	public function create(string $class, array $overrides = [])
	{
		return $this->from($class)->create($overrides);
	}

	public function from($class): Builder
	{
		if (!isset($this->defined[$class])) {
			throw new FabricaException("No definition found for $class");
		}

		return (new Builder($class, $this->defined[$class]))
			->onCreated(function ($entity) {
				if ($this->store) {
					$this->store->save($entity);
				}
			});
	}
}
