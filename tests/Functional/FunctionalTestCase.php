<?php

declare(strict_types=1);

namespace Tests\Functional;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use SykesCottages\Qu\Connector\RabbitMQ;
use SykesCottages\Qu\Message\RabbitMQMessage;

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
}