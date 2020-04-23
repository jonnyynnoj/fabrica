<?php declare(strict_types=1);

namespace Noj\Fabrica;

class Builder
{
	private $entityPopulator;

	private $class;
	private $definition;
	private $instances = 1;
	private $defineArguments = [];

	private $onCreated = [];
	private $onComplete = [];

	private static $created = [];
	private static $createdStack = [];

	public function __construct(string $class, callable $definition)
	{
		$this->entityPopulator = new EntityPopulator();
		$this->class = $class;
		$this->definition = $definition;
	}

	public function instances(int $instances): self
	{
		$this->instances = $instances;
		return $this;
	}

	public function defineArguments(array $defineArguments): self
	{
		$this->defineArguments = $defineArguments;
		return $this;
	}

	public function onCreated(callable $onCreated): self
	{
		$this->onCreated[] = $onCreated;
		return $this;
	}

	public function onComplete(callable $onComplete)
	{
		$this->onComplete[] = $onComplete;
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
			self::$created = [];
			self::$createdStack = [];
			throw $throwable;
		}
	}

	private function createEntity(array $overrides)
	{
		if (isset(self::$createdStack[$this->class])) {
			return self::$createdStack[$this->class];
		}

		$entity = new $this->class;
		self::$created[] = $entity;
		self::$createdStack[$this->class] = $entity;

		$attributes = ($this->definition)(...$this->defineArguments);
		$this->entityPopulator->populate($entity, array_merge($attributes));

		unset(self::$createdStack[$this->class]);

		if (empty(self::$createdStack)) {
			$this->entityPopulator->populate($entity, $overrides);
			$this->fireHandlers($this->onComplete, self::$created);
			self::$created = [];
		}

		$this->fireHandlers($this->onCreated, $entity);

		return $entity;
	}

	private function fireHandlers(array $handlers, $value)
	{
		foreach ($handlers as $handler) {
			$handler($value);
		}
	}
}
