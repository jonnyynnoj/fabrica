<?php declare(strict_types=1);

namespace Fabrica;

class Builder
{
	const IDENTIFIER_METHOD = '@';
	const IDENTIFIER_METHOD_CALL_MULTIPLE = '*';

	private $class;
	private $definition;
	private $instances = 1;
	private $onCreated = [];
	static private $createdCache = [];

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
		try {
			if ($this->instances === 1) {
				return $this->createEntity($overrides);
			}

			return array_map(function () use ($overrides) {
				return $this->createEntity($overrides);
			}, range(1, $this->instances));
		} catch (FabricaException $exception) {
			self::$createdCache = [];
			throw $exception;
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
		$this->populate($entity, $attributes);

		unset(self::$createdCache[$this->class]);
		return $entity;
	}

	private function populate($entity, array $attributes)
	{
		foreach ($attributes as $attribute => $value) {
			if (strpos($attribute, self::IDENTIFIER_METHOD) === 0) {
				$this->handleMethodCall($entity, $attribute, $value);
			} else {
				$entity->$attribute = $value;
			}
		}

		foreach ($this->onCreated as $callback) {
			$callback($entity);
		}
	}

	private function handleMethodCall($entity, $attribute, $value)
	{
		$multipleCallSymbol = self::IDENTIFIER_METHOD_CALL_MULTIPLE;
		$method = substr($attribute, 1);
		if (substr($method, -1) !== $multipleCallSymbol) {
			$this->applyMethodCall($entity, $method, $value);
			return;
		}

		if (!is_array($value)) {
			throw new FabricaException("$multipleCallSymbol method suffix can only be used for array values");
		}

		$method = substr($method, 0, -1);
		array_map(function ($item) use ($entity, $method) {
			$this->applyMethodCall($entity, $method, $item);
		}, $value);
	}

	private function applyMethodCall($entity, $method, $value)
	{
		if (!is_callable([$entity, $method])) {
			$class = get_class($entity);
			throw new FabricaException("Method $method does not exist on $class");
		}

		$entity->$method($value);
	}
}
