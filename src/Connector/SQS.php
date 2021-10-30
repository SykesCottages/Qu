<?php

declare(strict_types=1);

namespace SykesCottages\Qu\Connector;

use Aws\Sqs\SqsClient;
use SykesCottages\Qu\Connector\Contract\Queue;
use SykesCottages\Qu\Exception\InvalidMessageType;
use SykesCottages\Qu\Message\Contract\Message;
use SykesCottages\Qu\Message\SQSMessage;

use function json_encode;

class SQS extends SqsClient implements Queue
{
    private const LONG_POLL_TIME = 20;

    private const MAX_NUMBER_OF_MESSAGES_PER_POLL = 10;

    private const MIN_NUMBER_OF_MESSAGES_PER_POLL = 1;

    /** @var string[] */
    private array $queueOptions = [
        'blockingConsumer' => true,
        'pollTime' => self::LONG_POLL_TIME,
        'maxNumberOfMessagesPerConsume' => self::MIN_NUMBER_OF_MESSAGES_PER_POLL,
    ];

    /**
     * @param string[] $message
     */
    public function queueMessage(
        string $queue,
        array $message,
        ?string $messageId = null,
        ?string $duplicationId = null
    ): void {
        $message = [
            'QueueUrl' => $queue,
            'MessageBody' => json_encode($message),
            'MessageAttributes' => $this->getMessageAttributes($message),
        ];

        if ($messageId) {
            $message['MessageGroupId'] = $messageId;
        }

        if ($duplicationId) {
            $message['MessageDeduplicationId'] = $duplicationId;
        }

        $this->sendMessage($message);
    }

    public function consume(string $queue, callable $callback, callable $idleCallback): void
    {
        do {
            $message = $this->receiveMessage([
                'QueueUrl' => $queue,
                'WaitTimeSeconds' => $this->queueOptions['pollTime'],
                'MaxNumberOfMessages' => $this->getMaxNumberOfMessagesPerConsume(),
            ]);

            $messages = $message->get('Messages');

            if (! $messages) {
                $idleCallback();
                continue;
            }

            foreach ($messages as $message) {
                $callback(new SQSMessage($message));
            }
        } while ($this->queueOptions['blockingConsumer']);
    }

    public function acknowledge(string $queue, Message $message): void
    {
        $this->isMessageInTheCorrectFormat($message);

        $this->deleteMessage([
            'QueueUrl' => $queue,
            'ReceiptHandle' => $message->getReceiptHandle(),
        ]);
    }

    public function reject(string $queue, Message $message, string $errorMessage = ''): void
    {
        $this->isMessageInTheCorrectFormat($message);

        $this->changeMessageVisibility([
            'QueueUrl' => $queue,
            'ReceiptHandle' => $message->getReceiptHandle(),
            'VisibilityTimeout' => 0,
        ]);
    }

    /**
     * @param string[] $queueOptions
     */
    public function setQueueOptions(array $queueOptions): void
    {
        foreach ($queueOptions as $option => $value) {
            if (! isset($this->queueOptions[$option])) {
                continue;
            }

            $this->queueOptions[$option] = $value;
        }
    }

    /**
     * @param string[] $message
     *
     * @return string[][]
     */
    private function getMessageAttributes(array $message): array
    {
        $messageAttributes = [];
        foreach ($message as $key => $value) {
            if ($key === 'message') {
                continue;
            }

            $messageAttributes[$key] = [
                'DataType' => 'String',
                'StringValue' => $value,
            ];
        }

        return $messageAttributes;
    }

    private function isMessageInTheCorrectFormat(Message $message): bool
    {
        if (! $message instanceof SQSMessage) {
            throw new InvalidMessageType(SQSMessage::class);
        }

        return true;
    }

    private function getMaxNumberOfMessagesPerConsume(): int
    {
        if ($this->queueOptions['maxNumberOfMessagesPerConsume'] > self::MAX_NUMBER_OF_MESSAGES_PER_POLL) {
            return self::MAX_NUMBER_OF_MESSAGES_PER_POLL;
        }

        if ($this->queueOptions['maxNumberOfMessagesPerConsume'] < self::MIN_NUMBER_OF_MESSAGES_PER_POLL) {
            return self::MIN_NUMBER_OF_MESSAGES_PER_POLL;
        }

        return (int) $this->queueOptions['maxNumberOfMessagesPerConsume'];
    }
}
