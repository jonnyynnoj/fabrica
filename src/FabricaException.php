<?php declare(strict_types=1);

namespace Noj\Fabrica;

use Exception;

class FabricaException extends Exception
{
	public static function fromMissingDefinition(string $class, string $type): self
	{
		return new self("No definition found for $class:$type. Did you forget to define it?");
	}

	public static function doctrineNotConfigured(): self
	{
		return new self("Cannot retrieve the EntityManager as Fabrica isn't configured with a DoctrineStore");
	}
}
