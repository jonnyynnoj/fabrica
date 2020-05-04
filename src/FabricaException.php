<?php declare(strict_types=1);

namespace Noj\Fabrica;

class FabricaException extends \Exception
{
	public static function fromMissingDefinition(string $class, string $type): self
	{
		return new self("No definition found for $class:$type. Did you forget to define it?");
	}
}
