<?php

declare(strict_types=1);

namespace SykesCottages\Qu\Connector;

use Aws\Sqs\SqsClient;
use SykesCottages\Qu\Connector\Contract\QueueInterface;
use SykesCottages\Qu\Exception\InvalidMessageTypeException;
use SykesCottages\Qu\Message\Contract\Message;
use SykesCottages\Qu\Message\SQSMessage;

final class SQS extends SqsClient implements QueueInterface
{
    private const LONG_POLL_TIME = 20;

    private const DEAD_LETTER_SUFFIX = '-deadletter';

    public function queueMessage(string $queue, array $message): void
    {
        $this->sendMessage([
            'QueueUrl' => $queue,
            'MessageBody' => json_encode($message),
            'MessageAttributes' => $this->getMessageAttributes($message)
        ]);
    }

    public function consume(string $queue, callable $callback, callable $idleCallback): void
    {
        do {
            $message = $this->receiveMessage([
                'QueueUrl' => $queue,
                'WaitTimeSeconds' => self::LONG_POLL_TIME,
            ]);

            $messages = $message->get('Messages');

            if (!$messages) {
                $idleCallback();
                continue;
            }

            foreach ($messages as $message) {
                $callback(new SQSMessage($message));
            }
        } while (true);
    }

    public function acknowledge(string $queue, Message $message): void
    {
        if (!$message instanceof SQSMessage) {
            throw new InvalidMessageTypeException(SQSMessage::class);
        }

        $this->deleteMessage([
            'QueueUrl' => $queue,
            'ReceiptHandle' => $message->getReceiptHandle()
        ]);
    }

    public function reject(string $queue, Message $message, string $errorMessage = ''): void
    {
        if (!$message instanceof SQSMessage) {
            throw new InvalidMessageTypeException(SQSMessage::class);
        }

        $this->queueMessage($queue . self::DEAD_LETTER_SUFFIX, [
            'queue' => $queue,
            'body' => json_encode($message->getBody()),
            'error' => $errorMessage
        ]);

        $this->deleteMessage([
            'QueueUrl' => $queue,
            'ReceiptHandle' => $message->getReceiptHandle()
        ]);
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
}
