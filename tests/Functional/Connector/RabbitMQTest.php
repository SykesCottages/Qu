<?php

declare(strict_types = 1);

namespace Tests\Functional\Connector;

use PHPUnit\Framework\TestCase;
use SykesCottages\Qu\Connector\RabbitMQ;

class RabbitMQTest extends TestCase
{
    /**
     * @var RabbitMQ
     */
    private $rabbitMq;

    public function setUp(): void
    {
        $this->rabbitMq = new RabbitMQ(
            getenv('RABBIT_MQ_HOST'),
            (int)getenv('RABBIT_MQ_PORT'),
            getenv('RABBIT_MQ_USER'),
            getenv('RABBIT_MQ_PASSWORD')
        );
    }

    public function testWeCanConnectToTheRabbitMQServer(): void
    {
        $this->assertTrue($this->rabbitMq->isConnected());
    }
}