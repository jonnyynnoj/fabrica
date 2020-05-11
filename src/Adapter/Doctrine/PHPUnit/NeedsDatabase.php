<?php declare(strict_types=1);

namespace Noj\Fabrica\Adapter\Doctrine\PHPUnit;

if (version_compare(\PHPUnit\Runner\Version::id(), '8.0.0', '<')) {
	class_alias(NeedsDatabaseHooksForVersion7AndPrevious::class, NeedsDatabaseHooks::class);
} else {
	class_alias(NeedsDatabaseHooksForVersion8AndLater::class, NeedsDatabaseHooks::class);
}

trait NeedsDatabase
{
	use DatabaseAssertions;
	use NeedsDatabaseHooks;
}
