<?php declare(strict_types=1);

namespace Noj\Fabrica;

class Registry
{
	/** @var Definition[][] */
	private static $defined = [];

	public static function clear()
	{
		self::$defined = [];
	}

	public static function get(string $class, string $type): Definition
	{
		$definition = current(array_filter(
			self::$defined[$class] ?? [],
			function (Definition $definition) use ($type) {
				return $definition->type === $type;
			}
		));

		if (!$definition) {
			throw FabricaException::fromMissingDefinition($class, $type);
		}

		return $definition;
	}

	public static function register(Definition $definition): Definition
	{
		return self::$defined[$definition->class][] = $definition;
	}
}
