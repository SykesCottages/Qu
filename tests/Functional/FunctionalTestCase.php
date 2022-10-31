<?php

declare(strict_types=1);

namespace Tests\Functional;

use Mockery;
use PHPUnit\Framework\TestCase;

class FunctionalTestCase extends TestCase
{
    protected function assertFunctionIsNotCalled(): void
    {
        $this->assertTrue(false);
    }

    protected function assertFunctionHasBeenCalled(): void
    {
        $this->assertTrue(true);
    }

    public function tearDown(): void
    {
        $container = Mockery::getContainer();

        if ($container) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }

        parent::tearDown();
    }
}
