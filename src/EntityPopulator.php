<?php declare(strict_types=1);

namespace Fabrica;

class EntityPopulator
{
	const IDENTIFIER_METHOD = '@';
	const IDENTIFIER_METHOD_CALL_MULTIPLE = '*';

	public function populate($entity, array $attributes)
	{
		foreach ($attributes as $path => $value) {
			$this->handlePath($entity, $path, $value);
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

	private function handlePath($entity, string $path, $value)
	{
		$segments = explode('.', $path);
		$lastSegment = array_pop($segments);

		foreach ($segments as $i => $segment) {
			if ($this->isMethodCall($segment)) {
				$entity = $this->applyMethodCall($entity, $segment);
				continue;
			}

			$class = get_class($entity);

			if (preg_match('/(.+)\[(\d+)\]$/', $segment, $matches)) {
				$property = $entity->{$matches[1]};
				if (is_array($property) || $property instanceof \Traversable) {
					$entity = $property[$matches[2]];
					continue;
				}
			}

			if (!property_exists($entity, $segment)) {
				throw new FabricaException("Nested property $segment does not exist on $class");
			}

			$property = $entity->$segment;

			if (is_array($property) || $property instanceof \Traversable) {
				foreach ($property as $item) {
					$nextSegments = array_slice($segments, $i + 1);
					$nextPath = $nextSegments ? implode('.', $nextSegments) . ".$lastSegment" : $lastSegment;
					$this->handlePath($item, $nextPath, $value);
				}
				return;
			}

			if (!is_object($property)) {
				throw new FabricaException("Nested property $segment on $class is not an object");
			}

			$entity = $property;
		}

		if ($this->isMethodCall($lastSegment)) {
			$this->handleMethodCall($entity, $lastSegment, $value);
		} else {
			$entity->$lastSegment = $value;
		}
	}

	private function isMethodCall($attribute): bool
	{
		return strpos($attribute, self::IDENTIFIER_METHOD) === 0;
	}
}
