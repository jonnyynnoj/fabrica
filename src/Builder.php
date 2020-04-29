<?php declare(strict_types=1);

namespace Noj\Fabrica;

use Noj\Dot\Dot;

class Builder
{
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
	public function create(callable $overrides = null)
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

	private function createEntity(callable $overrides = null)
	{
		if (isset(self::$createdStack[$this->class])) {
			return self::$createdStack[$this->class];
		}

		$entity = new $this->class;
		self::$created[] = $entity;
		self::$createdStack[$this->class] = $entity;

		$attributes = ($this->definition)(...$this->defineArguments);
		$overriddenAttributes = is_callable($overrides) ? $overrides(...$this->defineArguments) : [];

		(new Dot($entity))->set(array_merge($attributes, $overriddenAttributes));

		unset(self::$createdStack[$this->class]);

		if (empty(self::$createdStack)) {
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
