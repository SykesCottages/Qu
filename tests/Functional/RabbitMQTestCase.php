<?php

declare(strict_types=1);

namespace Tests\Functional;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use SykesCottages\Qu\Connector\RabbitMQ;
use SykesCottages\Qu\Message\RabbitMQMessage;

class RabbitMQTestCase extends FunctionalTestCase
{
    protected RabbitMQ $rabbitMq;

    protected AMQPChannel $channel;

    public function tearDown(): void
    {
        parent::tearDown();

        $this->channel->close();
    }

    protected function assertQueueIsEmpty(string $queueName): void
    {
        $message = $this->channel->basic_get($queueName);
        $this->assertNull($message);
    }

    protected function assertQueueHasAMessage(string $queueName): void
    {
        $message = $this->channel->basic_get($queueName);
        $this->assertInstanceOf(AMQPMessage::class, $message);
    }

    protected function cleanUpMessages(string $queueName): void
    {
        $this->channel->queue_purge($queueName);
    }

    protected function consumeOneMessage(string $queueName, string $callbackFunctionName): void
    {
        $this->channel->basic_consume(
            $queueName,
            '',
            false,
            false,
            false,
            false,
            function (AMQPMessage $message) use ($queueName, $callbackFunctionName) : void {
                $this->rabbitMq->{$callbackFunctionName}($queueName, new RabbitMQMessage($message));
            }
        );

        $this->channel->wait();
    }
}
