<?php declare(strict_types=1);

namespace Fabrica;

class EntityPopulator
{
	const IDENTIFIER_METHOD = '@';
	const IDENTIFIER_METHOD_CALL_MULTIPLE = '*';

	public function populate($entity, array $attributes)
	{
		foreach ($attributes as $attribute => $value) {
			if (strpos($attribute, self::IDENTIFIER_METHOD) === 0) {
				$this->handleMethodCall($entity, $attribute, $value);
			} else {
				$entity->$attribute = $value;
			}
		}
	}

	private function handleMethodCall($entity, $attribute, $value)
	{
		$method = substr($attribute, 1);
		if (substr($method, -1) !==  self::IDENTIFIER_METHOD_CALL_MULTIPLE) {
			$this->applyMethodCall($entity, $method, $value);
			return;
		}

		if (!is_array($value)) {
			throw new FabricaException(
				self::IDENTIFIER_METHOD_CALL_MULTIPLE . ' method suffix can only be used for array values'
			);
		}

		$method = substr($method, 0, -1);

		foreach ($value as $item) {
			$this->applyMethodCall($entity, $method, $item);
		}
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
