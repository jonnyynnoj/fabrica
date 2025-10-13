<?php declare(strict_types=1);

namespace Noj\Fabrica\Adapter\Doctrine\PHPUnit;

trait NeedsDatabase
{
	use DatabaseAssertions;
	use NeedsDatabaseHooks;
}
