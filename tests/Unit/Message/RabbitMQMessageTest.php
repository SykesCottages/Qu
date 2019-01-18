<?php

declare(strict_types = 1);

namespace Tests\Message;

use Mockery;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use SykesCottages\Qu\Message\RabbitMQMessage;
use Tests\Unit\UnitTestCase;

class RabbitMQMessageTest extends UnitTestCase
{
    /**
     * @var AMQPMessage
     */
    private $amqpMessage;
    /**
     * @var RabbitMQMessage
     */
    private $rabbitMqMessage;

    public function setUp(): void
    {
        $this->amqpMessage = Mockery::mock(AMQPMessage::class);
        $this->rabbitMqMessage = new RabbitMQMessage($this->amqpMessage);
    }

    public function testTheBodyIsReturnedAsAnAssociativeArray(): void
    {
        $this->amqpMessage
            ->shouldReceive('getBody')
            ->once()
            ->withNoArgs()
            ->andReturn('{"test":"example"}');

        $expectedResult = [
            'test' => 'example'
        ];

        $this->assertSame($expectedResult, $this->rabbitMqMessage->getBody());
    }

    public function testTheBodyIsEmptyIfBlankJSONStringIsReturned(): void
    {
        $this->amqpMessage
            ->shouldReceive('getBody')
            ->once()
            ->withNoArgs()
            ->andReturn('{}');

        $this->assertEmpty($this->rabbitMqMessage->getBody());
    }

    public function testGetDeliveryInfoChannelReturnsWithAChannel(): void
    {
        $this->amqpMessage
            ->shouldReceive('get')
            ->once()
            ->with('channel')
            ->andReturn(Mockery::mock(AMQPChannel::class));

        $this->assertInstanceOf(AMQPChannel::class, $this->rabbitMqMessage->getDeliveryInfoChannel());
    }

    public function testGetDeliveryTagReturnsWithString(): void
    {
        $randomDeliveryTag = 'THIS IS A RANDOM STRING';

        $this->amqpMessage
            ->shouldReceive('get')
            ->once()
            ->with('delivery_tag')
            ->andReturn($randomDeliveryTag);

        $this->assertSame($randomDeliveryTag, $this->rabbitMqMessage->getDeliveryTag());
    }
}
