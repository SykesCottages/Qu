<?php

declare(strict_types=1);

namespace SykesCottages\Qu\Connector\Contract;

use SykesCottages\Qu\Message\Contract\Message;

interface Queue
{
    /**
     * @param string[] $message
     */
    public function queueMessage(string $queue, array $message, string $messageId = null, string $duplicationId = null) : void;

    public function consume(string $queue, callable $callback, callable $idleCallback) : void;

    public function acknowledge(string $queue, Message $message) : void;

    public function reject(string $queue, Message $message, string $errorMessage = '') : void;

    /**
     * @param string[] $queueOptions
     */
    public function setQueueOptions(array $queueOptions) : void;
}
