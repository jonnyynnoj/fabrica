<?php declare(strict_types=1);

namespace Noj\Fabrica;

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

	public static function create(string $class, callable $overrides = null)
	{
		return self::of($class)->create($overrides);
	}

	public static function createMany(string $class, int $amount, callable $overrides = null)
	{
		return self::of($class)
			->instances($amount)
			->create($overrides);
	}

	public static function of($class, string $type = Definition::DEFAULT_TYPE): Builder
	{
		return (new Builder($class, Registry::get($class, $type)))
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
