<?php declare(strict_types=1);

namespace Noj\Fabrica;

use function Noj\Dot\set;

class Definition
{
	private $defaults;
	private $callbacks = [];
	private $attributes = [];

	public function __construct(callable $defaults)
	{
		$this->defaults = $defaults;
		$this->onCreated([$this, 'applyCallableProperties']);
	}

	public function getAttributes(callable $overrides = null, ...$args): array
	{
		return $this->attributes = array_merge(
			($this->defaults)(...$args),
			is_callable($overrides) ? $overrides(...$args) : []
		);
	}

	private function applyCallableProperties($entity)
	{
		foreach ($this->attributes as $attribute => $value) {
			if ($value instanceof CallableProperty) {
				set($entity, $attribute, $value->apply($entity));
			}
		}
	}

	public function onCreated(callable $callback): self
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
}
