<?php declare(strict_types=1);

namespace Fabrica;

class Builder
{
	private $class;
	private $definition;
	private $entityPopulator;

	private $instances = 1;
	private $onCreated = [];

	private static $createdCache = [];

	public function __construct(string $class, callable $definition)
	{
		$this->class = $class;
		$this->definition = $definition;
		$this->entityPopulator = new EntityPopulator();
	}

	public function instances(int $instances)
	{
		$this->instances = $instances;
		return $this;
	}

	public function onCreated(callable $onCreated)
	{
		$this->onCreated[] = $onCreated;
		return $this;
	}

	public function create(array $overrides = [])
	{
		try {
			if ($this->instances === 1) {
				return $this->createEntity($overrides);
			}

			return array_map(function () use ($overrides) {
				return $this->createEntity($overrides);
			}, range(1, $this->instances));
		} catch (\Throwable $throwable) {
			self::$createdCache = [];
			throw $throwable;
		}
	}

	private function createEntity(array $overrides)
	{
		if (isset(self::$createdCache[$this->class])) {
			return self::$createdCache[$this->class];
		}

		$entity = new $this->class;
		self::$createdCache[$this->class] = $entity;

		$attributes = array_merge(($this->definition)(), $overrides);
		$this->entityPopulator->populate($entity, $attributes);

		foreach ($this->onCreated as $callback) {
			$callback($entity);
		}

		unset(self::$createdCache[$this->class]);
		return $entity;
	}
}
