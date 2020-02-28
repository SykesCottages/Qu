<?php

declare(strict_types=1);

namespace Tests\Functional\Connector;

use SykesCottages\Qu\Connector\RabbitMQ;
use SykesCottages\Qu\Message\Contract\Message;
use SykesCottages\Qu\Message\RabbitMQMessage;
use Tests\Functional\RabbitMQTestCase;
use function getenv;

class RabbitMQTest extends RabbitMQTestCase
{
    private const QUEUE_NAME = 'test';

    private const DEAD_LETTER_QUEUE_NAME = 'dead_letter';

    private const NUMBER_OF_MESSAGES_IN_BATCH = 10;

    public function setUp() : void
    {
        $this->rabbitMq = new RabbitMQ(
            getenv('RABBIT_MQ_HOST'),
            (int) getenv('RABBIT_MQ_PORT'),
            getenv('RABBIT_MQ_USER'),
            getenv('RABBIT_MQ_PASSWORD')
        );

        $this->channel = $this->rabbitMq->channel();

        $this->cleanUpMessages(self::QUEUE_NAME);
        $this->cleanUpMessages(self::DEAD_LETTER_QUEUE_NAME);
    }

    public function testWeCanConnectToTheRabbitMQServer() : void
    {
        $this->assertTrue(
            $this->rabbitMq->isConnected()
        );
    }

    public function testWeCanAcknowledgeMessageAndDeleteItFromTheQueue() : void
    {
        $this->addMessageToQueue();

        $this->consumeOneMessage(self::QUEUE_NAME, 'acknowledge');

        $this->assertQueueIsEmpty(self::QUEUE_NAME);
    }

    public function testWeCanRejectAMessageAndSendItToTheDeadLetterQueue() : void
    {
        $this->addMessageToQueue();

        $this->consumeOneMessage(self::QUEUE_NAME, 'reject');

        $this->assertQueueIsEmpty(self::QUEUE_NAME);
        $this->assertQueueHasAMessage(self::DEAD_LETTER_QUEUE_NAME);
    }

    public function testWeCanCallTheCallbackFunctionWhenWeHaveAMessage() : void
    {
        $this->rabbitMq->setQueueOptions([
            'blockingConsumer' => false,
            'non-existing-option' => true,
        ]);

        $this->addMessageToQueue();

        $this->rabbitMq->consume(
            self::QUEUE_NAME,
            function (Message $message) : void {
                $this->assertFunctionHasBeenCalled();
                $this->assertInstanceOf(RabbitMQMessage::class, $message);
            },
            function () : void {
                $this->assertFunctionIsNotCalled();
            }
        );
    }

    public function testWeCanBatchMultipleMessagesAndTheyAppearInTheQueue() : void
    {
        $numberOfMessagesToTestWith = self::NUMBER_OF_MESSAGES_IN_BATCH;

        $this->addMultipleMessagesToQueueAsBatch($numberOfMessagesToTestWith);

        while ($numberOfMessagesToTestWith-- > 0) {
            $this->assertQueueHasAMessage(self::QUEUE_NAME);
        }

        $this->assertQueueIsEmpty(self::QUEUE_NAME);
    }

    private function addMessageToQueue() : void
    {
        $this->rabbitMq->queueMessage(self::QUEUE_NAME, ['example' => 'test']);
    }

    private function addMultipleMessagesToQueueAsBatch(int $numberOfMessagesInBatch) : void
    {
        $messageBatch = [];

        while ($numberOfMessagesInBatch-- > 0) {
            $messageBatch[] = [
                'example' => 'test',
                'number' => $numberOfMessagesInBatch,
            ];
        }

        $this->rabbitMq->queueBatch(self::QUEUE_NAME, $messageBatch);
    }
}
