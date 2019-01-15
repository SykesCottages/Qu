<?php

declare(strict_types = 1);

namespace Example;

use SykesCottages\Qu\Consumer\Consumer;
use SykesCottages\Qu\Message\Contract\Message;

class TestConsumer extends Consumer
{

    /**
     * Implement this function to process the message
     *
     * @param Message $message
     */
    public function process(Message $message): void
    {
        var_dump($message->getBody());
        $this->queue->acknowledge($message);
    }
}