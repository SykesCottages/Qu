<?php

declare(strict_types=1);

namespace Tests\Message;

use SykesCottages\Qu\Message\SQSMessage;
use Tests\Unit\UnitTestCase;

class SQSMessageTest extends UnitTestCase
{
    private const RECEIPT_HANDLE = 'this-is-the-receipt-handle';

    /** @var string[] */
    private $message;
    /** @var SQSMessage */
    private $sqsMessage;

    public function setUp(): void
    {
        $this->message = [
            'Body' => '{"test": "example"}',
            'ReceiptHandle' => self::RECEIPT_HANDLE,
        ];

        $this->sqsMessage = new SQSMessage($this->message);
    }

    public function testTheBodyIsReturnedAsAnAssociativeArray(): void
    {
        $expectedResult = ['test' => 'example'];

        $this->assertSame($expectedResult, $this->sqsMessage->getBody());
    }

    public function testGetReceiptHandleReturnsTheCorrectString(): void
    {
        $this->assertSame(self::RECEIPT_HANDLE, $this->sqsMessage->getReceiptHandle());
    }

    public function testRawMessageReturnsTheEntireMessage(): void
    {
        $this->assertSame($this->message, $this->sqsMessage->getRawMessage());
    }
}
