<?php declare(strict_types=1);

namespace Fabrica\Fabrica;

class Fabrica
{
	/** @var callable[] */
	private $defined = [];

	public function define(string $class, callable $definition)
	{
		$this->defined[$class] = $definition;
	}

	public function create(string $class)
	{
		$attributes = $this->defined[$class]();
		$entity = new $class;

		foreach ($attributes as $attribute => $value) {
			$entity->$attribute = $value;
		}

		return $entity;
	}
}
