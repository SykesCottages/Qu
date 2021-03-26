<?php

declare(strict_types=1);

namespace Tests\Functional\Connector;

use Mockery;
use SykesCottages\Qu\Connector\SQS;
use SykesCottages\Qu\Message\Contract\Message;
use SykesCottages\Qu\Message\SQSMessage;
use Tests\Functional\Connector\Stubs\SQSCallable;
use Tests\Functional\SQSTestCase;
use function getenv;

class SQSTest extends SQSTestCase
{
    private const DEFAULT_NUMBER_OF_URLS = 2;

    private const MAX_NUMBER_OF_MESSAGES_PER_POLL = 10;

    private const MIN_NUMBER_OF_MESSAGES_PER_POLL = 1;

    private const QUEUE_NAME = 'test';

    private const NUMBER_OF_MESSAGES_IN_BATCH = 10;

    public function setUp() : void
    {
        $this->sqs = new SQS(
            [
                'service' => 'sqs',
                'endpoint' => getenv('SQS_ENDPOINT'),
                'region' => 'elasticmq',
                'credentials' => [
                    'key' => 'X',
                    'secret' => 'X',
                ],
                'version' => '2012-11-05',
                'exception_class' => 'Aws\Exception\AwsException',
            ]
        );

        $this->testingQueueUrl = getenv('SQS_ENDPOINT') . '/queue/' . self::QUEUE_NAME;

        $this->sqs->setQueueOptions(
            [
                'blockingConsumer' => false,
                'pollTime' => 0,
                'non-existing-option' => true,
            ]
        );

        $this->purgeQueue();
    }

    public function testWeCanConnectToSQSAndReturnAListOfQueueUrls() : void
    {
        $activeQueues = $this->sqs->listQueues();

        $urls = $activeQueues->get('QueueUrls');

        $this->assertCount(self::DEFAULT_NUMBER_OF_URLS, $urls);
    }

    public function testWeCanAcknowledgeAMessageInTheQueue() : void
    {
        $this->addMessageToQueue();

        $this->assertQueueHasAtLeastOneMessageWithAcknowledgement();

        $this->assertQueueIsEmpty();
    }

    public function testWeCanRejectAMessageInTheQueue() : void
    {
        $this->addMessageToQueue();

        $this->assertQueueHasAtLeastOneMessageAndRejectMessage();

        $this->assertQueueIsEmpty();

        $this->assertDeadLetterQueueHasAtLeastOneMessage();
    }

    public function testWeCanCallTheCallbackFunctionOnConsume() : void
    {
        $this->addMessageToQueue();

        $this->sqs->consume(
            $this->testingQueueUrl,
            function (Message $message) : void {
                $this->assertFunctionHasBeenCalled();
                $this->assertInstanceOf(SQSMessage::class, $message);
            },
            function () : void {
                $this->assertFunctionIsNotCalled();
            }
        );
    }

    public function testWeCanCallTheIdleCallbackFunctionOnConsume() : void
    {
        $this->sqs->consume(
            $this->testingQueueUrl,
            function (Message $message) : void {
                $this->assertFunctionIsNotCalled();
            },
            function () : void {
                $this->assertFunctionHasBeenCalled();
            }
        );
    }

    /**
     * @return int[][]
     */
    public function retrieveMinAndMaxDataProvider() : array
    {
        return [
            'Test if we specify more than the MAX it will cap to the MAX value allowed by SQS' => [
                100,
                self::MAX_NUMBER_OF_MESSAGES_PER_POLL,
            ],
            'Test if we specify less than the MIN value it will cap to the MIN value' => [
                0,
                self::MIN_NUMBER_OF_MESSAGES_PER_POLL,
            ],
        ];
    }

    /**
     * @dataProvider retrieveMinAndMaxDataProvider
     */
    public function testWeCanOnlyRetrieveMessagesBetweenTheMaxAndMin(
        int $numberOfMessagesToConsumeAtATime,
        int $expectedNumberOfCallbackCalls
    ) : void {
        $this->addMultipleMessagesToQueue(100);

        $this->sqs->setQueueOptions(['maxNumberOfMessagesPerConsume' => $numberOfMessagesToConsumeAtATime]);

        $mock = Mockery::mock(SQSCallable::class);

        $mock->shouldReceive('__invoke')
            ->times($expectedNumberOfCallbackCalls);

        $this->sqs->consume(
            $this->testingQueueUrl,
            $mock,
            function () : void {
                $this->assertFunctionIsNotCalled();
            }
        );
    }

    public function testWeCanSendABatchOfMessagesToQueue() : void
    {
        $numberOfMessagesToTestWith = self::NUMBER_OF_MESSAGES_IN_BATCH;

        $this->addMultipleMessagesToQueueAsBatch($numberOfMessagesToTestWith);

        while ($numberOfMessagesToTestWith-- > 0) {
            $this->assertQueueHasAtLeastOneMessage();
        }

        $this->assertQueueIsEmpty();
    }
}
