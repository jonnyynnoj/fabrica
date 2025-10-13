<?php declare(strict_types=1);

namespace Noj\Fabrica;

use Noj\Dot\Dot;

class Registry
{
	/** @var Definition[][] */
	private static array $defined = [];

	public static function clear(): void
	{
		self::$defined = [];
	}

	public static function get(string $class, string $type): Definition
	{
		$definition = current(array_filter(
			self::$defined[$class] ?? [],
			static fn(Definition $definition) => $definition->type === $type
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
