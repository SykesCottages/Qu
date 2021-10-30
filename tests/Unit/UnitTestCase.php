<?php

declare(strict_types=1);

namespace Tests\Unit;

use Mockery;
use PHPUnit\Framework\TestCase;

class UnitTestCase extends TestCase
{
    public function tearDown(): void
    {
        $container = Mockery::getContainer();

        if ($container) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }

        parent::tearDown();
        Mockery::close();
    }
}
