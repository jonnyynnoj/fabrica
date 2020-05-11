<?php declare(strict_types=1);

namespace Noj\Fabrica\Adapter\Doctrine\PHPUnit;

use PHPUnit\Runner\AfterTestHook;
use PHPUnit\Runner\BeforeTestHook;

class RefreshDatabase implements BeforeTestHook, AfterTestHook
{
	use NeedsDatabase;

	public function executeBeforeTest(string $test): void
	{
		$this->setUp();
	}

	public function executeAfterTest(string $test, float $time): void
	{
		$this->tearDown();
	}
}
