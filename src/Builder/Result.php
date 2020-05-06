<?php declare(strict_types=1);

namespace Noj\Fabrica\Builder;

use Noj\Fabrica\Definition;

class Result
{
	public $definition;
	public $entity;

	public function __construct(Definition $definition, $entity)
	{
		$this->definition = $definition;
		$this->entity = $entity;
	}
}
