<?php

declare(strict_types=1);

namespace SykesCottages\Qu\Message;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use SykesCottages\Qu\Message\Contract\Message;

use function json_decode;

class RabbitMQMessage implements Message
{
    public function __construct(private AMQPMessage $message)
    {
    }

    // @codingStandardsIgnoreStart
    /** @return mixed array */
    public function getBody(): array
    {
        return json_decode($this->message->getBody(), true);
        //@codingStandardsIgnoreEnd
    }

    public function getDeliveryInfoChannel(): AMQPChannel
    {
        return $this->message->get('channel');
    }

    public function getDeliveryTag(): string
    {
        return (string) $this->message->get('delivery_tag');
    }
}
