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
		return $this->of($class)->create($overrides);
	}

	public function of($class, int $instances = 1): Builder
	{
		if (!isset($this->defined[$class])) {
			throw new FabricaException("No definition found for $class");
		}

		return (new Builder($class, $this->defined[$class]))
			->instances($instances)
			->onComplete(function ($entities) {
				if ($this->store) {
					foreach ($entities as $entity) {
						$this->store->save($entity);
					}
				}
			});
	}
}
