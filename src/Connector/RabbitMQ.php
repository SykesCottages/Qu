<?php

declare(strict_types=1);

namespace SykesCottages\Qu\Connector;

use PhpAmqpLib\Channel\AMQPChannel;
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

    private const DEFAULT_IS_GLOBAL = false;

    private const PREFETCH_COUNT = 1;

    /**
     * @var AMQPChannel
     */
    private $channel;
    /**
     * @var array
     */
    private $queueOptions = [
        'blockingConsumer' => true,
        'prefetchSize' => self::DEFAULT_PREFETCH_SIZE,
        'prefetchCount' => self::PREFETCH_COUNT
    ];

    public function queueMessage(string $queue, array $message): void
    {
        $this->connectToChannel();

        $this->channel->basic_publish(
            new AMQPMessage(json_encode($message)),
            $queue
        );
    }

    public function consume(string $queue, callable $callback, callable $idleCallback): void
    {
        $this->connectToChannel();

        $this->channel->basic_qos(
            $this->queueOptions['prefetchSize'],
            $this->queueOptions['prefetchCount'],
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

        do {
            $this->channel->wait();
        } while ($this->queueOptions['blockingConsumer']);
    }

    public function acknowledge(string $queue, Message $message): void
    {
        $this->isMessageInTheCorrectFormat($message);

        $message
            ->getDeliveryInfoChannel()
            ->basic_ack($message->getDeliveryTag());
    }

    public function reject(string $queue, Message $message, string $errorMessage = ''): void
    {
        $this->isMessageInTheCorrectFormat($message);

        $message
            ->getDeliveryInfoChannel()
            ->basic_nack($message->getDeliveryTag());
    }

    public function setQueueOptions(array $queueOptions): void
    {
        foreach ($queueOptions as $option => $value) {
            if (isset($this->queueOptions[$option])) {
                $this->queueOptions[$option] = $value;
            }
        }
    }

    private function isMessageInTheCorrectFormat(Message $message): bool
    {
        if (!$message instanceof RabbitMqMessage) {
            throw new InvalidMessageTypeException(RabbitMQMessage::class);
        }

        return true;
    }

    private function connectToChannel(): bool
    {
        if (!$this->channel) {
            $this->channel = $this->channel();
        }

        return $this->isConnected();
    }
}
