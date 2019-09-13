<?php

declare(strict_types=1);

namespace SykesCottages\Qu\Connector;

use Aws\Sqs\SqsClient;
use SykesCottages\Qu\Connector\Contract\QueueInterface;
use SykesCottages\Qu\Exception\InvalidMessageTypeException;
use SykesCottages\Qu\Message\Contract\Message;
use SykesCottages\Qu\Message\SQSMessage;

class SQS extends SqsClient implements QueueInterface
{
    private const LONG_POLL_TIME = 20;

    private const MAX_NUMBER_OF_MESSAGES_PER_POLL = 10;

    private const MIN_NUMBER_OF_MESSAGES_PER_POLL = 1;

    /**
     * @var array
     */
    private $queueOptions = [
        'blockingConsumer' => true,
        'pollTime' => self::LONG_POLL_TIME,
        'maxNumberOfMessagesPerConsume' => self::MIN_NUMBER_OF_MESSAGES_PER_POLL
    ];

    public function queueMessage(string $queue, array $message): void
    {
        $this->sendMessage(
            ['QueueUrl' => $queue] + $this->wrapMessageInSQSFormat($message)
        );
    }

    public function queueBatch(string $queue, array $messages) : void
    {
        $batchedMessages = [];
        foreach ($messages as $message) {
            $batchedMessages[] = $this->wrapMessageInSQSFormat($message);
        }

        $this->sendMessageBatch([
            'QueueUrl' => $queue,
            'Entries' => $batchedMessages,
        ]);
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

            if (!$messages) {
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
            'ReceiptHandle' => $message->getReceiptHandle()
        ]);
    }

    public function reject(string $queue, Message $message, string $errorMessage = ''): void
    {
        $this->isMessageInTheCorrectFormat($message);

        $this->changeMessageVisibility([
            'QueueUrl' => $queue,
            'ReceiptHandle' => $message->getReceiptHandle(),
            'VisibilityTimeout' => 0
        ]);
    }

    public function setQueueOptions(array $queueOptions): void
    {
        foreach ($queueOptions as $option => $value) {
            if (isset($this->queueOptions[$option])) {
                $this->queueOptions[$option] = $value;
            }
        }
    }

    private function wrapMessageInSQSFormat(array $message): array
    {
        return [
            'MessageBody' => json_encode($message),
            'MessageAttributes' => $this->getMessageAttributes($message)
        ];
    }

    private function getMessageAttributes(array $message): array
    {
        $messageAttributes = [];
        foreach ($message as $key => $value) {
            $messageAttributes[$key] = [
                'DataType' => 'String',
                'StringValue' => $value
            ];
        }
        return $messageAttributes;
    }

    private function isMessageInTheCorrectFormat(Message $message)
    {
        if (!$message instanceof SQSMessage) {
            throw new InvalidMessageTypeException(SQSMessage::class);
        }

        return true;
    }

    private function getMaxNumberOfMessagesPerConsume() : int
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
