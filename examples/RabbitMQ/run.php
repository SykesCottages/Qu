<?php

declare(strict_types = 1);

include '../../vendor/autoload.php';

use Example\TestConsumer;
use SykesCottages\Qu\Connector\Queue;
use SykesCottages\Qu\Connector\RabbitMQ;

$rabbitMq = new RabbitMQ('localhost', 48888, 'admin', 'admin');

$testingQueue = new Queue('test', $rabbitMq);
// Create a new consumer for the test queue
$consumer = new TestConsumer($testingQueue);

$testingQueue->queueMessage(['example' => rand(1, 10000)]);

// Start consuming the messages
$consumer->start();
