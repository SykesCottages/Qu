<?php

declare(strict_types=1);

namespace SykesCottages\Qu\Message\Contract;

interface MessageHandler
{
    public function acknowledge(Message $message): void;

    public function reject(Message $message, string $errorMessage = ''): void;
}
