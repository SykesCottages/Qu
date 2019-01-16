<?php

declare(strict_types=1);

namespace Tests\Unit\Connector;

use Mockery;
use Mockery\Mock;
use SykesCottages\Qu\Connector\RabbitMQ;
use SykesCottages\Qu\Connector\SQS;
use SykesCottages\Qu\Exception\InvalidMessageTypeException;
use SykesCottages\Qu\Message\Contract\Message;
use Tests\Unit\UnitTestCase;

class RabbitMQTest extends UnitTestCase
{
    private const QUEUE_NAME = 'test';

    /**
     * @var Mock|Message
     */
    private $genericMessage;
    /**
     * @var RabbitMQ
     */
    private $rabbitMQ;

    public function setUp(): void
    {
        $this->genericMessage = Mockery::mock(Message::class);

        $this->rabbitMQ = new RabbitMQ(
            getenv('RABBIT_MQ_HOST'),
            (int)getenv('RABBIT_MQ_PORT'),
            getenv('RABBIT_MQ_USER'),
            getenv('RABBIT_MQ_PASSWORD')
        );
    }

    /**
     * @param string $functionName
     * @dataProvider functionDataProvider
     */
    public function testExceptionIsThrownWhenInvalidMessageIsPassed(string $functionName): void
    {
        $this->expectException(InvalidMessageTypeException::class);

        $this->expectExceptionMessage('Message is not the correct type: SykesCottages\Qu\Message\RabbitMQMessage');

        $this->rabbitMQ->{$functionName}(self::QUEUE_NAME, $this->genericMessage);
    }

    public function functionDataProvider(): array
    {
        return [
            'test reject returns the correct exception' => [
                'reject'
            ],
            'test acknowledge returns the correct exception' => [
                'acknowledge'
            ]
        ];
    }
}
