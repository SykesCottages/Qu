<?php

declare(strict_types = 1);

namespace SykesCottages\Qu\Message;

use SykesCottages\Qu\Message\Contract\Message;

class SQSMessage implements Message
{
    /**
     * @var array
     */
    private $message;

    public function __construct(array $message)
    {
        $this->message = $message;
    }

    public function getBody() : ?array
    {
        return json_decode($this->message['Body'], true);
    }

    public function getReceiptHandle() : string
    {
        return $this->message['ReceiptHandle'];
    }

    public function getRawMessage() : array
    {
        return $this->message;
    }
}
