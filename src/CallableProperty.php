<?php declare(strict_types=1);

namespace Noj\Fabrica;

class CallableProperty
{
	private $callable;

	public function __construct(callable $callable)
	{
		$this->callable = $callable;
	}

	public function apply($entity)
	{
		return ($this->callable)($entity);
	}
}
