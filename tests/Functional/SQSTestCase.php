<?php

declare(strict_types=1);

namespace Tests\Functional;

use SykesCottages\Qu\Connector\SQS;
use SykesCottages\Qu\Message\SQSMessage;
use function current;

class SQSTestCase extends FunctionalTestCase
{
    /** @var SQS */
    protected $sqs;
    /** @var string */
    protected $testingQueueUrl;

    /**
     * @return SQSMessage[]
     */
    protected function getMessages(string $queueUrl) : array
    {
        $message = $this->sqs->receiveMessage(
            [
                'QueueUrl' => $queueUrl,
                'WaitTimeSeconds' => 0,
            ]
        );

        return $message->get('Messages') ?? [];
    }

    protected function addMultipleMessagesToQueueAsBatch(int $numberOfMessagesInBatch) : void
    {
        $messageBatch = [];

        while ($numberOfMessagesInBatch-- > 0) {
            $messageBatch[] = [
                'example' => 'test',
                'number' => $numberOfMessagesInBatch,
            ];
        }

        $this->sqs->queueBatch($this->testingQueueUrl, $messageBatch);
    }

    protected function addMultipleMessagesToQueue(int $messagesToSend) : void
    {
        $countOfMessages = 0;
        while ($countOfMessages < $messagesToSend) {
            $this->addMessageToQueue();
            $countOfMessages++;
        }
    }

    protected function addMessageToQueue() : void
    {
        $this->sqs->queueMessage($this->testingQueueUrl, ['example' => 'test']);
    }

    protected function assertQueueIsEmpty() : void
    {
        $this->assertEmpty(
            $this->getMessages($this->testingQueueUrl)
        );
    }

    protected function assertQueueHasAtLeastOneMessage() : void
    {
        $messages = $this->getMessages($this->testingQueueUrl);
        $this->assertCount(1, $messages);
    }

    protected function assertDeadLetterQueueHasAtLeastOneMessage() : void
    {
        $messages = $this->getMessages($this->testingQueueUrl . '-deadletter');
        $this->assertCount(1, $messages);
    }

    protected function assertQueueHasAtLeastOneMessageWithAcknowledgement() : void
    {
        $messages = $this->getMessages($this->testingQueueUrl);
        $this->assertCount(1, $messages);

        $this->sqs->acknowledge($this->testingQueueUrl, new SQSMessage(current($messages)));
    }

    protected function assertQueueHasAtLeastOneMessageAndRejectMessage() : void
    {
        $messages = $this->getMessages($this->testingQueueUrl);
        $this->assertCount(1, $messages);

        $this->sqs->reject($this->testingQueueUrl, new SQSMessage(current($messages)));
    }

    protected function purgeQueue() : void
    {
        $this->sqs->purgeQueue(
            [
                'QueueUrl' => $this->testingQueueUrl,
            ]
        );
    }
}
