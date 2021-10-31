<?php

declare(strict_types=1);

namespace Tests\Unit\Connector;

use Mockery;
use Mockery\Mock;
use SykesCottages\Qu\Connector\SQS;
use SykesCottages\Qu\Exception\InvalidMessageType;
use SykesCottages\Qu\Message\Contract\Message;
use Tests\Unit\UnitTestCase;

class SQSTest extends UnitTestCase
{
    private const QUEUE_NAME = 'test';

    /** @var Mock|Message */
    private $genericMessage;
    /** @var SQS */
    private $sqs;

    public function setUp(): void
    {
        $this->genericMessage = Mockery::mock(Message::class);

        $this->sqs = new SQS([
            'service' => 'sqs',
            'region' => 'elasticmq',
            'version' => '2012-11-05',
            'exception_class' => 'Aws\Exception\AwsException',
        ]);
    }

    /**
     * @dataProvider functionDataProvider
     */
    public function testExceptionIsThrownWhenInvalidMessageIsPassed(string $functionName): void
    {
        $this->expectException(InvalidMessageType::class);

        $this->expectExceptionMessage('Message is not the correct type: SykesCottages\Qu\Message\SQSMessage');

        $this->sqs->{$functionName}(self::QUEUE_NAME, $this->genericMessage);
    }

    /**
     * @return string[][]
     */
    public function functionDataProvider(): array
    {
        return [
            'test reject returns the correct exception' => ['reject'],
            'test acknowledge returns the correct exception' => ['acknowledge'],
        ];
    }
}
