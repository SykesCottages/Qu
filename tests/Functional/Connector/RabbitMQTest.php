<?php

declare(strict_types=1);

namespace Tests\Functional\Connector;

use SykesCottages\Qu\Connector\RabbitMQ;
use Tests\Functional\RabbitMQTestCase;

class RabbitMQTest extends RabbitMQTestCase
{
    private const QUEUE_NAME = 'test';

    private const DEAD_LETTER_QUEUE_NAME = 'dead_letter';

    public function setUp(): void
    {
        $this->rabbitMq = new RabbitMQ(
            getenv('RABBIT_MQ_HOST'),
            (int)getenv('RABBIT_MQ_PORT'),
            getenv('RABBIT_MQ_USER'),
            getenv('RABBIT_MQ_PASSWORD')
        );

        $this->channel = $this->rabbitMq->channel();
    }

    public function testWeCanConnectToTheRabbitMQServer(): void
    {
        $this->assertTrue(
            $this->rabbitMq->isConnected()
        );
    }

    public function testWeCanAcknowledgeMessageAndDeleteItFromTheQueue(): void
    {
        $this->rabbitMq->queueMessage(self::QUEUE_NAME, ['example' => 'test']);

        $this->consumeOneMessage(self::QUEUE_NAME, 'acknowledge');

        $this->assertQueueIsEmpty(self::QUEUE_NAME);
    }

    public function testWeCanRejectAMessageAndSendItToTheDeadLetterQueue(): void
    {
        $this->rabbitMq->queueMessage(self::QUEUE_NAME, ['example' => 'test']);

        $this->consumeOneMessage(self::QUEUE_NAME, 'reject');

        $this->assertQueueIsEmpty(self::QUEUE_NAME);
        $this->assertQueueHasAMessage(self::DEAD_LETTER_QUEUE_NAME);
    }
}