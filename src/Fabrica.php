<?php declare(strict_types=1);

namespace Noj\Fabrica;

use Noj\Fabrica\Store\StoreInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use function Noj\Dot\get;

class Fabrica
{
	const DEFAULT_TYPE = 'default';

	/** @var StoreInterface|null */
	private static $store;

	/** @var Definition[] */
	private static $defined = [];

	private static $defineArguments = [];

	public static function init(StoreInterface $store = null)
	{
		self::$store = $store;
		self::$defined = [];
	}

	public static function define(
		string $class,
		callable $attributes,
		string $type = self::DEFAULT_TYPE,
		$extends = null
	): Definition {
		$definition = new Definition($attributes);

		if ($extends === true) {
			$extends = self::DEFAULT_TYPE;
		}

		if ($extends && isset(self::$defined[$class][$extends])) {
			$definition->extend(self::$defined[$class][$extends]);
		}

		return self::$defined[$class][$type] = $definition;
	}

	public static function create(string $class, callable $overrides = null)
	{
		return self::of($class)->create($overrides);
	}

	public static function of($class, string $type = self::DEFAULT_TYPE): Builder
	{
		if (!isset(self::$defined[$class])) {
			throw new FabricaException("No definition found for $class");
		}

		return (new Builder($class, self::$defined[$class][$type]))
			->defineArguments(self::$defineArguments)
			->onComplete(function ($entities) {
				if (self::$store) {
					foreach ($entities as $entity) {
						self::$store->save($entity[0]);
					}
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
