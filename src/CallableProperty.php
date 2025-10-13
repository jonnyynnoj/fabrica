<?php declare(strict_types=1);

namespace Noj\Fabrica;

class CallableProperty
{
	public function __construct(private \Closure $closure)
	{
	}

	public function apply(object $entity): mixed
	{
		return ($this->closure)($entity);
	}
}
