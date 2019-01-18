<?php

declare(strict_types = 1);

namespace Tests\Unit\Consumer\Stub;

use SykesCottages\Qu\Consumer\Consumer;
use SykesCottages\Qu\Message\Contract\Message;

class TestConsumer extends Consumer
{
    public function process(Message $message): void
    {
        $message->getBody();
        $this->queue->acknowledge($message);
    }
}
