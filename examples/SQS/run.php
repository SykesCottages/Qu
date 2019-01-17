<?php

declare(strict_types=1);

include '../../vendor/autoload.php';

use Example\TestConsumer;
use SykesCottages\Qu\Connector\Queue;
use SykesCottages\Qu\Connector\SQS;

$sqs = new SQS([
    'service' => 'sqs',
    'endpoint' => 'http://localhost:41662',
    'region' => 'elasticmq',
    'credentials' => [
        'key' => 'X',
        'secret' => 'X',
    ],
    'version' => '2012-11-05',
    'exception_class' => 'Aws\Exception\AwsException'
]);

$sqs->setQueueOptions([
    'blockingConsumer' => true,
]);

$testingQueue = new Queue('http://localhost:41662/queue/test', $sqs);
// Create a new consumer for the test queue
$consumer = new TestConsumer($testingQueue);

$testingQueue->queueMessage(['example' => rand(1, 10000)]);

// Start consuming the messages
$consumer->start();
