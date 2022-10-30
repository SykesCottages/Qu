<?php

declare(strict_types=1);

namespace SykesCottages\Qu\Exception;

use LogicException;

use function sprintf;

class InvalidMessageType extends LogicException
{
    protected $message = 'Message is not the correct type: %s';

    public function __construct(string $messageType)
    {
        parent::__construct(sprintf($this->message, $messageType));
    }
}
