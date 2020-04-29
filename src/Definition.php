<?php declare(strict_types=1);

namespace Noj\Fabrica;

class Definition
{
	private $attributes;
	private $callbacks = [];

	public function __construct(callable $attributes)
	{
		$this->attributes = $attributes;
	}

	public function getAttributes(callable $overrides = null, ...$args): array
	{
		$attributes = ($this->attributes)(...$args);
		$overriddenAttributes = is_callable($overrides) ? $overrides(...$args) : [];
		return array_merge($attributes, $overriddenAttributes);
	}

	public function onCreated(callable $callback)
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
