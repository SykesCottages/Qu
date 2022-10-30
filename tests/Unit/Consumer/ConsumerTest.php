<?php

declare(strict_types=1);

namespace Tests\Unit\Consumer;

use Mockery;
use Mockery\Mock;
use SykesCottages\Qu\Connector\Contract\Queue as QueueInterface;
use SykesCottages\Qu\Connector\Queue;
use SykesCottages\Qu\Consumer\Consumer;
use SykesCottages\Qu\Exception\ExitRequested;
use Tests\Unit\Consumer\Stub\TestConsumer;
use Tests\Unit\UnitTestCase;

use function sprintf;

class ConsumerTest extends UnitTestCase
{
    private const QUEUE_NAME = 'test';

    private TestConsumer|Consumer $consumer;

    private Queue $queueConnector;

    private $queueProvider;

    public function setUp(): void
    {
        $this->queueProvider  = Mockery::mock(QueueInterface::class);
        $this->queueConnector = new Queue(self::QUEUE_NAME, $this->queueProvider);
        $this->consumer       = new TestConsumer($this->queueConnector);
    }

    public function testConsumerIsStartedOnTheCorrectQueueObject(): void
    {
        $this->queueProvider
            ->shouldReceive('consume')
            ->once()
            ->with(self::QUEUE_NAME, [$this->consumer, 'process'], [$this->consumer, 'idle'])
            ->andReturnSelf();

        $this->consumer->start();
    }

    public function testExceptionIsThrownIfItHasBeenRequested(): void
    {
        $this->expectExceptionMessage(
            sprintf('Exit has been requested for the queue: %s', self::QUEUE_NAME)
        );

        $this->expectException(ExitRequested::class);

        $this->consumer->requestExit();
        $this->consumer->idle();
    }

    public function testExceptionIsNotThrownWhenItHasNotBeenRequested(): void
    {
        $this->assertNull(
            $this->consumer->idle()
        );
    }
}
