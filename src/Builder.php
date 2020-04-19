<?php declare(strict_types=1);

namespace Fabrica;

class Builder
{
	private $class;
	private $definition;
	private $instances = 1;
	private $onCreated = [];

	public function __construct(string $class, callable $definition)
	{
		$this->class = $class;
		$this->definition = $definition;
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
		if ($this->instances === 1) {
			return $this->createEntity($overrides);
		}

		return array_map(function () use ($overrides) {
			return $this->createEntity($overrides);
		}, range(1, $this->instances));
	}

	private function createEntity(array $overrides)
	{
		$attributes = array_merge(($this->definition)(), $overrides);
		$entity = new $this->class;

		foreach ($attributes as $attribute => $value) {
			if (strpos($attribute, '@') === 0) {
				$method = substr($attribute, 1);
				$entity->$method($value);
			} else {
				$entity->$attribute = $value;
			}
		}

		foreach ($this->onCreated as $callback) {
			$callback($entity);
		}

		return $entity;
	}
}
