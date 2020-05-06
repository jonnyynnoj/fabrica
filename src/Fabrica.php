<?php declare(strict_types=1);

namespace Noj\Fabrica;

use Noj\Fabrica\Builder\Builder;
use Noj\Fabrica\Store\StoreInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use function Noj\Dot\get;

class Fabrica
{
	/** @var StoreInterface|null */
	private static $store;

	private static $defineArguments = [];

	public static function init(StoreInterface $store = null)
	{
		self::$store = $store;
		Registry::clear();
	}

	public static function define(string $class, callable $attributes): Definition
	{
		$definition = new Definition($class, $attributes);
		return Registry::register($definition);
	}

	public static function create(string $class, ...$args)
	{
		$type = $args && is_string($args[0]) ? array_shift($args) : Definition::DEFAULT_TYPE;
		$overrides = $args && is_callable($args[0]) ? $args[0] : null;

		return self::of($class, $type)
			->create($overrides);
	}

	public static function createMany(string $class, int $amount, callable $overrides = null)
	{
		return self::of($class)
			->instances($amount)
			->create($overrides);
	}

	public static function createType(string $class, string $type, callable $overrides = null)
	{
		return self::of($class, $type)
			->create($overrides);
	}

	public static function of($class, string $type = Definition::DEFAULT_TYPE): Builder
	{
		return (new Builder($class, Registry::get($class, $type)))
			->defineArguments(self::$defineArguments)
			->onComplete(function (array $results) {
				if (self::$store) {
					self::$store->save($results);
				}
			});
	}

	public static function call(callable $callable): CallableProperty
	{
		return new CallableProperty($callable);
	}

	public static function property(string $path): CallableProperty
	{
		return self::call(function ($entity) use ($path) {
			return get($entity, $path);
		});
	}

	public static function loadFactories(array $paths)
	{
		foreach ($paths as $path) {
			$directory = new RecursiveDirectoryIterator($path);
			$iterator = new RecursiveIteratorIterator($directory);
			$files = new RegexIterator($iterator, '/^.+\.php$/i');

			/** @var \SplFileInfo $file */
			foreach ($files as $file) {
				require $file->getPathname();
			}
		}
	}

	public static function addDefineArgument($argument)
	{
		self::$defineArguments[] = $argument;
	}
}
