<?php

declare(strict_types=1);

namespace Example;

use SykesCottages\Qu\Consumer\Consumer;
use SykesCottages\Qu\Message\Contract\Message;
use function var_dump;

class TestConsumer extends Consumer
{
    public function process(Message $message) : void
    {
        var_dump($message->getBody());
        $this->queue->acknowledge($message);
    }
}
