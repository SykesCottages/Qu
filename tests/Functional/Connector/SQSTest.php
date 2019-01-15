<?php

declare(strict_types = 1);

namespace Tests\Functional\Connector;

use PHPUnit\Framework\TestCase;
use SykesCottages\Qu\Connector\SQS;

class SQSTest extends TestCase
{
    private const DEFAULT_NUMBER_OF_URLS = 2;

    /**
     * @var SQS
     */
    private $sqs;

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
    }

    public function testWeCanConnectToSQSAndReturnAListOfQueueUrls(): void
    {
        $activeQueues = $this->sqs->listQueues();

        $urls = $activeQueues->get('QueueUrls');

        $this->assertCount(self::DEFAULT_NUMBER_OF_URLS, $urls);
    }
}