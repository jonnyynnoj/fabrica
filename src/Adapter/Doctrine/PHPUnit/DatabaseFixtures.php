<?php declare(strict_types=1);

namespace Noj\Fabrica\Adapter\Doctrine\PHPUnit;

if (version_compare(\PHPUnit\Runner\Version::id(), '8.0.0', '<')) {
	class_alias(DatabaseFixturesForVersion7AndPrevious::class, DatabaseFixturesSetup::class);
} else {
	class_alias(DatabaseFixturesForVersion8AndLater::class, DatabaseFixturesSetup::class);
}

trait DatabaseFixtures
{
	use DatabaseAssertions;
	use DatabaseFixturesSetup;
}
