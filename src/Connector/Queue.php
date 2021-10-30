<?php

declare(strict_types=1);

namespace SykesCottages\Qu\Connector;

use SykesCottages\Qu\Connector\Contract\Queue as QueueInterface;
use SykesCottages\Qu\Consumer\Contract\Consumable;
use SykesCottages\Qu\Message\Contract\Message;
use SykesCottages\Qu\Message\Contract\MessageHandler;

final class Queue implements Consumable, MessageHandler
{
    /** @var string */
    private $name;

    /** @var Queue */
    private $queue;

    public function __construct(string $name, QueueInterface $queue)
    {
        $this->name  = $name;
        $this->queue = $queue;
    }

    public function getQueueName(): string
    {
        return $this->name;
    }

    /**
     * @param string[] $body
     */
    public function queueMessage(array $body): void
    {
        $this->queue->queueMessage($this->name, $body);
    }

    public function consume(callable $callback, callable $idleCallback): void
    {
        $this->queue->consume($this->name, $callback, $idleCallback);
    }

    public function acknowledge(Message $message): void
    {
        $this->queue->acknowledge($this->name, $message);
    }

    public function reject(Message $message, string $errorMessage = ''): void
    {
        $this->queue->reject($this->name, $message, $errorMessage);
    }
}
