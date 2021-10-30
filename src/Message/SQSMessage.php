<?php

declare(strict_types=1);

namespace SykesCottages\Qu\Message;

use SykesCottages\Qu\Message\Contract\Message;

use function json_decode;

class SQSMessage implements Message
{
    /** @var string[] */
    private $message;

    /**
     * @param string[] $message
     */
    public function __construct(array $message)
    {
        $this->message = $message;
    }

    /**
     * @return string[]|null
     */
    public function getBody(): ?array
    {
        return json_decode($this->message['Body'], true);
    }

    public function getReceiptHandle(): string
    {
        return $this->message['ReceiptHandle'];
    }

    /**
     * @return string[]
     */
    public function getRawMessage(): array
    {
        return $this->message;
    }
}
