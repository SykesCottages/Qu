<?php

declare(strict_types=1);

namespace Tests\Exception;

use LogicException;
use SykesCottages\Qu\Exception\InvalidMessageType;
use Tests\Unit\UnitTestCase;

class InvalidMessageTypeExceptionTest extends UnitTestCase
{
    public function testExceptionMatchesLogicExceptionClass() : void
    {
        $exception = new InvalidMessageType(
            self::class
        );

        $this->assertInstanceOf(LogicException::class, $exception);
    }

    /**
     * @dataProvider classMessageDataProvider
     */
    public function testExceptionProducesCorrectMessageBasedOnClassName(
        string $className,
        string $expectedResult
    ) : void {
        $exception = new InvalidMessageType($className);
        $this->assertSame($expectedResult, $exception->getMessage());
    }

    /**
     * @return string[][]
     */
    public function classMessageDataProvider() : array
    {
        return [
            'Test when class name is passed the full path is returned in the error message' => [
                self::class,
                'Message is not the correct type: Tests\Exception\InvalidMessageTypeExceptionTest',
            ],
            'Test when partial class name is passed in then the correct message is returned' => [
                'SykesCottages\Qu\Example',
                'Message is not the correct type: SykesCottages\Qu\Example',
            ],
        ];
    }
}
