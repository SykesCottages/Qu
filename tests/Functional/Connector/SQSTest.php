<?php

declare(strict_types=1);

namespace Tests\Functional\Connector;

use SykesCottages\Qu\Connector\SQS;
use SykesCottages\Qu\Message\Contract\Message;
use SykesCottages\Qu\Message\SQSMessage;
use Tests\Functional\FunctionalTestCase;

class SQSTest extends FunctionalTestCase
{
    private const DEFAULT_NUMBER_OF_URLS = 2;

    private const QUEUE_NAME = 'test';

    /**
     * @var SQS
     */
    private $sqs;
    /**
     * @var string
     */
    private $testingQueueUrl;

    public function setUp(): void
    {
        $this->sqs = new SQS([
            'service' => 'sqs',
            'endpoint' => getenv('SQS_ENDPOINT'),
            'region' => 'elasticmq',
            'credentials' => [
                'key' => 'X',
                'secret' => 'X',
            ],
            'version' => '2012-11-05',
            'exception_class' => 'Aws\Exception\AwsException'
        ]);

        $this->testingQueueUrl = getenv('SQS_ENDPOINT') . '/queue/' . self::QUEUE_NAME;

        $this->sqs->setQueueOptions([
            'blockingConsumer' => false,
            'pollTime' => 0
        ]);

        $this->sqs->purgeQueue([
            'QueueUrl' => $this->testingQueueUrl
        ]);
    }

    public function testWeCanConnectToSQSAndReturnAListOfQueueUrls(): void
    {
        $activeQueues = $this->sqs->listQueues();

        $urls = $activeQueues->get('QueueUrls');

        $this->assertCount(self::DEFAULT_NUMBER_OF_URLS, $urls);
    }

    public function testWeCanAcknowledgeAMessageInTheQueue(): void
    {
        $this->addMessageToQueue();

        $messages = $this->getMessages($this->testingQueueUrl);

        $this->assertCount(1, $messages);

        $this->sqs->acknowledge($this->testingQueueUrl, new SQSMessage(current($messages)));

        $this->assertEmpty($this->getMessages($this->testingQueueUrl));
    }

    public function testWeCanRejectAMessageInTheQueue(): void
    {
        $this->addMessageToQueue();

        $messages = $this->getMessages($this->testingQueueUrl);

        $this->assertCount(1, $messages);

        $this->sqs->reject($this->testingQueueUrl, new SQSMessage(current($messages)));

        $this->assertEmpty($this->getMessages($this->testingQueueUrl));

        $messages = $this->getMessages($this->testingQueueUrl . '-deadletter');

        $this->assertCount(1, $messages);
    }

    public function testWeCanCallTheCallbackFunctionOnConsume(): void
    {
        $this->addMessageToQueue();

        $this->sqs->consume(
            $this->testingQueueUrl,
            function (Message $message) {
                $this->assertFunctionHasBeenCalled();
                $this->assertInstanceOf(SQSMessage::class, $message);
            },
            function () {
                $this->assertFunctionIsNotCalled();
            }
        );
    }

    public function testWeCanCallTheIdleCallbackFunctionOnConsume(): void
    {
        $this->sqs->consume(
            $this->testingQueueUrl,
            function (Message $message) {
                $this->assertFunctionIsNotCalled();
            },
            function () {
                $this->assertFunctionHasBeenCalled();
            }
        );
    }

    private function addMessageToQueue(): void
    {
        $this->sqs->queueMessage($this->testingQueueUrl, ['example' => 'test']);
    }

    private function getMessages(string $queueUrl): array
    {
        $message = $this->sqs->receiveMessage([
            'QueueUrl' => $queueUrl,
            'WaitTimeSeconds' => 0,
        ]);

        return $message->get('Messages') ?? [];
    }
}
