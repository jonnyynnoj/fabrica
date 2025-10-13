<?php declare(strict_types=1);

namespace Noj\Fabrica\Builder;

use Noj\Fabrica\Definition;
use Throwable;
use function Noj\Dot\set;

class Builder
{
	private int $instances = 1;
	private array $defineArguments = [];
	private array $onComplete = [];

	/** @var Result[] */
	private static array $created = [];
	private static int $stackCount = 0;

	public function __construct(private string $class, private Definition $definition)
	{
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

	public function onComplete(\Closure $onComplete): self
	{
		$this->onComplete[] = $onComplete;
		return $this;
	}

	public function create(?\Closure $overrides = null): array|object
	{
		try {
			if ($this->instances === 1) {
				return $this->createEntity($overrides);
			}

			return array_map(
				fn() => $this->createEntity($overrides, false),
				range(1, $this->instances)
			);
		} catch (Throwable $throwable) {
			self::$created = [];
			self::$stackCount = 0;
			throw $throwable;
		}
	}

	private function createEntity(?\Closure $overrides = null, bool $useCache = true): object
	{
		if (!$overrides && $useCache && isset(self::$created[$this->class])) {
			return self::$created[$this->class]->entity;
		}

		$entity = new $this->class;
		self::$created[$this->class] = new Result($this->definition, $entity);
		++self::$stackCount;

		$attributes = $this->definition->getAttributes($overrides, ...$this->defineArguments);
		set($entity, $attributes);

		if (--self::$stackCount === 0) {
			$this->cleanUp();
		}

		return $entity;
	}

	private function cleanUp(): void
	{
		foreach (self::$created as $result) {
			$result->definition->fireCallbacks($result->entity, ...$this->defineArguments);
		}

		foreach ($this->onComplete as $handler) {
			$handler(self::$created);
		}

		self::$created = [];
	}
}
