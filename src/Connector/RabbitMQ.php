<?php

declare(strict_types=1);

namespace SykesCottages\Qu\Connector;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPLazyConnection;
use PhpAmqpLib\Message\AMQPMessage;
use SykesCottages\Qu\Connector\Contract\Queue;
use SykesCottages\Qu\Exception\InvalidMessageType;
use SykesCottages\Qu\Message\Contract\Message;
use SykesCottages\Qu\Message\RabbitMQMessage;

use function json_encode;

class RabbitMQ extends AMQPLazyConnection implements Queue
{
    private const CONSUMER_TAG = 'default.consumer.tag';

    private const DEFAULT_PREFETCH_SIZE = null;

    private const DEFAULT_IS_GLOBAL = false;

    private const PREFETCH_COUNT = 1;

    private AMQPChannel $channel;

    /** @var string[] */
    private array $queueOptions = [
        'blockingConsumer' => true,
        'prefetchSize' => self::DEFAULT_PREFETCH_SIZE,
        'prefetchCount' => self::PREFETCH_COUNT,
        'consumerTag' => self::CONSUMER_TAG,
    ];

    /** @param string[] $message */
    public function queueMessage(
        string $queue,
        array $message,
        string|null $messageId = null,
        string|null $duplicationId = null,
    ): void {
        $this->connectToChannel();

        $this->channel->basic_publish(
            new AMQPMessage(json_encode($message)),
            $queue,
        );
    }

    public function consume(string $queue, callable $callback, callable $idleCallback): void
    {
        $this->connectToChannel();

        $this->channel->basic_qos(
            $this->queueOptions['prefetchSize'],
            $this->queueOptions['prefetchCount'],
            self::DEFAULT_IS_GLOBAL,
        );

        $this->channel->basic_consume(
            $queue,
            $this->queueOptions['consumerTag'],
            false,
            false,
            false,
            false,
            static function (AMQPMessage $message) use ($callback): void {
                $callback(new RabbitMQMessage($message));
            },
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

    /** @param string[] $queueOptions */
    public function setQueueOptions(array $queueOptions): void
    {
        foreach ($queueOptions as $option => $value) {
            if (! isset($this->queueOptions[$option])) {
                continue;
            }

            $this->queueOptions[$option] = $value;
        }
    }

    private function isMessageInTheCorrectFormat(Message $message): bool
    {
        if (! $message instanceof RabbitMQMessage) {
            throw new InvalidMessageType(RabbitMQMessage::class);
        }

        return true;
    }

    private function connectToChannel(): bool
    {
        if (! $this->channel) {
            $this->channel = $this->channel();
        }

        return $this->isConnected();
    }
}
