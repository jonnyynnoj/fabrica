<?php declare(strict_types=1);

namespace Noj\Fabrica;

use Noj\Fabrica\Store\StoreInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class Fabrica
{
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

	public static function define(string $class, callable $definition): Definition
	{
		return self::$defined[$class] = new Definition($definition);
	}

	public static function create(string $class, callable $overrides = null)
	{
		return self::of($class)->create($overrides);
	}

	public static function of($class, int $instances = 1): Builder
	{
		if (!isset(self::$defined[$class])) {
			throw new FabricaException("No definition found for $class");
		}

		return (new Builder($class, self::$defined[$class]))
			->instances($instances)
			->defineArguments(self::$defineArguments)
			->onComplete(function ($entities) {
				if (self::$store) {
					foreach ($entities as $entity) {
						self::$store->save($entity[0]);
					}
				}
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
