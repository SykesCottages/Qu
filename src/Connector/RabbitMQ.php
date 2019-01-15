<?php

declare(strict_types = 1);

namespace SykesCottages\Qu\Connector;

use PhpAmqpLib\Connection\AMQPLazyConnection;
use PhpAmqpLib\Message\AMQPMessage;
use SykesCottages\Qu\Connector\Contract\QueueInterface;
use SykesCottages\Qu\Exception\InvalidMessageTypeException;
use SykesCottages\Qu\Message\Contract\Message;
use SykesCottages\Qu\Message\RabbitMQMessage;

final class RabbitMQ extends AMQPLazyConnection implements QueueInterface
{
    private const CONSUMER_TAG = '.consumer.tag';

    private const DEFAULT_PREFETCH_SIZE = null;

    private const DEFAULT_IS_GLOBAL = null;

    private const PREFETCH_COUNT = 1;

    private $deadLetterQueue;
    private $channel;

    public function __construct(
        string $host,
        int $port,
        string $user = 'guest',
        string $pass = 'guest',
        string $deadLetterQueue = 'dead_letter'
    ) {
        parent::__construct($host, $port, $user, $pass);

        $this->channel = $this->channel();
        $this->deadLetterQueue = $deadLetterQueue;
    }

    public function queueMessage(string $queue, array $message): void
    {
        $this->channel->basic_publish(
            new AMQPMessage(json_encode($message)),
            $queue
        );
    }

    public function consume(string $queue, callable $callback, callable $idleCallback): void
    {
        $this->channel->basic_qos(
            self::DEFAULT_PREFETCH_SIZE,
            self::PREFETCH_COUNT,
            self::DEFAULT_IS_GLOBAL
        );

        $this->channel->basic_consume(
            $queue,
            $queue . self::CONSUMER_TAG,
            false,
            false,
            false,
            false,
            function (AMQPMessage $message) use ($callback) {
                $callback(new RabbitMQMessage($message));
            }
        );

        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }

    public function acknowledge(string $queue, Message $message): void
    {
        if (!$message instanceof RabbitMqMessage) {
            throw new InvalidMessageTypeException(RabbitMQMessage::class);
        }

        $message->getDeliveryInfoChannel()->basic_ack($message->getDeliveryTag());
    }

    public function reject(string $queue, Message $message, string $errorMessage = ''): void
    {
        if (!$message instanceof RabbitMqMessage) {
            throw new InvalidMessageTypeException(RabbitMQMessage::class);
        }

        $deadLetterMessage = [
            'queue' => $queue,
            'body' => $message->getBody(),
            'error' => $errorMessage,
        ];

        $messageChannel = $message->getDeliveryInfoChannel();

        $messageChannel->basic_publish(
            new AMQPMessage(json_encode($deadLetterMessage)),
            $this->deadLetterQueue
        );

        $messageChannel->basic_nack($message->getDeliveryTag());
    }
}
