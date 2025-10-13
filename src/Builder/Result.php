<?php declare(strict_types=1);

namespace Noj\Fabrica\Builder;

use Noj\Fabrica\Definition;

class Result
{
	public function __construct(public Definition $definition, public object $entity)
	{
	}
}
