<?php

declare(strict_types = 1);

namespace SykesCottages\Qu\Connector\Contract;

use SykesCottages\Qu\Message\Contract\Message;

interface QueueInterface
{
    public function queueMessage(string $queue, array $message): void;

    public function consume(string $queue, callable $callback, callable $idleCallback): void;

    public function acknowledge(string $queue, Message $message): void;

    public function reject(string $queue, Message $message, string $errorMessage = ''): void;
}
