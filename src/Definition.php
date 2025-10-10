<?php declare(strict_types=1);

namespace Noj\Fabrica;

use function Noj\Dot\set;

class Definition
{
	public const DEFAULT_TYPE = 'default';

	private \Closure $defaults;
	private array $attributes = [];
	private ?Definition $parent = null;

	/** @var \Closure[] */
	private array $callbacks = [];

	public string $type = self::DEFAULT_TYPE;

	public function __construct(public string $class, ?\Closure $defaults = null)
	{
		$this->defaults = $defaults ?? function() {
			return [];
		};

		$this->onCreated(\Closure::fromCallable([$this, 'applyCallableProperties']));
	}

	public function getAttributes(?\Closure $overrides = null, ...$args): array
	{
		$parentAttributes = $this->parent?->getAttributes(null, ...$args) ?? [];

		return $this->attributes = array_merge(
			$parentAttributes,
			($this->defaults)(...$args),
			is_callable($overrides) ? $overrides(...$args) : []
		);
	}

	private function applyCallableProperties(object $entity): void
	{
		foreach ($this->attributes as $attribute => $value) {
			if ($value instanceof CallableProperty) {
				set($entity, $attribute, $value->apply($entity));
			}
		}
	}

	public function onCreated(\Closure $callback): self
	{
		$this->callbacks[] = $callback;
		return $this;
	}

	public function fireCallbacks($entity, ...$args)
	{
		foreach ($this->callbacks as $callback) {
			$callback($entity, ...$args);
		}
	}

	public function type(string $type): self
	{
		$this->type = $type;
		return $this;
	}

	public function extends(string $class, string $type = self::DEFAULT_TYPE): self
	{
		$this->parent = Registry::get($class, $type);
		return $this;
	}
}
