<?php

declare(strict_types = 1);

namespace SykesCottages\Qu\Consumer;

use SykesCottages\Qu\Connector\Queue;
use SykesCottages\Qu\Exception\ExitRequestedException;
use SykesCottages\Qu\Message\Contract\Message;

abstract class Consumer
{
    /**
     * @var bool
     */
    protected $exitRequested;
    /**
     * @var Queue
     */
    protected $queue;

    public function __construct(Queue $queue)
    {
        $this->queue = $queue;
    }

    /**
     * Implement this function to process the message
     *
     * @param Message $message
     */
    abstract public function process(Message $message): void;

    public function start(): void
    {
        $this->queue->consume([$this, 'process'], [$this, 'idle']);
    }

    public function idle(): void
    {
        if ($this->exitRequested) {
            throw new ExitRequestedException($this->queue->getQueueName());
        }
    }

    public function requestExit(): void
    {
        $this->exitRequested = true;
    }
}
