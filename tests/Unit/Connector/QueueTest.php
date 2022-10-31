<?php

declare(strict_types=1);

namespace Tests\Unit\Connector;

use Mockery;
use Mockery\Mock;
use SykesCottages\Qu\Connector\Contract\Queue as QueueInterface;
use SykesCottages\Qu\Connector\Queue;
use SykesCottages\Qu\Message\Contract\Message;
use Tests\Unit\UnitTestCase;

class QueueTest extends UnitTestCase
{
    private const QUEUE_NAME = 'test_queue';

    // @codingStandardsIgnoreStart
    /** @var Mock|Queue */
    private $queueInterface;

    /** @var Queue */
    private $queue;
    // @codingStandardsIgnoreEnd

    public function setUp(): void
    {
        $this->queueInterface = Mockery::mock(QueueInterface::class);
        $this->queue          = new Queue(
            self::QUEUE_NAME,
            $this->queueInterface,
        );
    }

    public function testQueueCanPutMessagesInTheCorrectQueue(): void
    {
        $message = ['test' => 'example'];

        $this->queueInterface
            ->shouldReceive('queueMessage')
            ->once()
            ->with(self::QUEUE_NAME, $message)
            ->andReturnSelf();

        $this->queue->queueMessage($message);
    }

    public function testQueueCanCallTheCorrectConsumeMethodOnTheCorrectQueue(): void
    {
        $callback = static function (): void {
        };

        $idleCallback = static function (): void {
        };

        $this->queueInterface
            ->shouldReceive('consume')
            ->once()
            ->with(self::QUEUE_NAME, $callback, $idleCallback)
            ->andReturnSelf();

        $this->queue->consume($callback, $idleCallback);
    }

    public function testQueueCanAcknowledgeMessageInQueue(): void
    {
        $message = Mockery::mock(Message::class);

        $this->queueInterface
            ->shouldReceive('acknowledge')
            ->once()
            ->with(self::QUEUE_NAME, $message)
            ->andReturnSelf();

        $this->queue->acknowledge($message);
    }

    public function testQueueCanRejectMessageInQueue(): void
    {
        $message      = Mockery::mock(Message::class);
        $errorMessage = 'This is a sample error';

        $this->queueInterface
            ->shouldReceive('reject')
            ->once()
            ->with(self::QUEUE_NAME, $message, $errorMessage)
            ->andReturnSelf();

        $this->queue->reject($message, $errorMessage);
    }
}
