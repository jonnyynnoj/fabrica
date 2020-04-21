<?php declare(strict_types=1);

namespace Fabrica;

class EntityPopulator
{
	const IDENTIFIER_METHOD = '@';
	const IDENTIFIER_METHOD_CALL_MULTIPLE = '*';
	const IDENTIFIER_NESTED_PROPERTY = '.';

	public function populate($entity, array $attributes)
	{
		foreach ($attributes as $attribute => $value) {
			if (strpos($attribute, self::IDENTIFIER_NESTED_PROPERTY) !== false) {
				$this->handleNestedProperty($entity, $attribute, $value);
			} elseif ($this->isMethodCall($attribute)) {
				$this->handleMethodCall($entity, $attribute, $value);
			} else {
				$entity->$attribute = $value;
			}
		}
	}

	private function handleMethodCall($entity, $attribute, $value)
	{
		if (substr($attribute, -1) !== self::IDENTIFIER_METHOD_CALL_MULTIPLE) {
			$this->applyMethodCall($entity, $attribute, $value);
			return;
		}

		if (!is_array($value)) {
			throw new FabricaException(
				self::IDENTIFIER_METHOD_CALL_MULTIPLE . ' method suffix can only be used for array values'
			);
		}

		$method = substr($attribute, 0, -1);

		foreach ($value as $item) {
			$this->applyMethodCall($entity, $method, $item);
		}
	}

	private function applyMethodCall($entity, $attribute, $value = null)
	{
		$method = substr($attribute, 1);
		if (!is_callable([$entity, $method])) {
			$class = get_class($entity);
			throw new FabricaException("Method $method does not exist on $class");
		}

		return $entity->$method($value);
	}

	private function handleNestedProperty($entity, string $attribute, $value)
	{
		$segments = explode('.', $attribute);
		$last = array_pop($segments);
		foreach ($segments as $i => $segment) {
			if ($this->isMethodCall($segment)) {
				$entity = $this->applyMethodCall($entity, $segment);
				continue;
			}

			$class = get_class($entity);

			if (!property_exists($entity, $segment)) {
				throw new FabricaException("Nested property $segment does not exist on $class");
			}

			$property = $entity->$segment;

			if (is_array($property) || $property instanceof \Traversable) {
				foreach ($property as $item) {
					$remainingSegments = array_slice($segments, $i + 1);
					$path = $remainingSegments ? implode(self::IDENTIFIER_NESTED_PROPERTY, $remainingSegments) . ".$last" : $last;
					$this->handleNestedProperty($item, $path, $value);
				}
				return;
			}

			if (!is_object($property)) {
				throw new FabricaException("Nested property $segment on $class is not an object");
			}

			$entity = $property;
		}

		$this->populate($entity, [$last => $value]);
	}

	private function isMethodCall($attribute): bool
	{
		return strpos($attribute, self::IDENTIFIER_METHOD) === 0;
	}
}
