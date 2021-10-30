<?php

declare(strict_types=1);

namespace SykesCottages\Qu\Exception;

use LogicException;

use function sprintf;

class InvalidMessageType extends LogicException
{
    public function __construct(string $messageType)
    {
        $this->message = 'Message is not the correct type: %s';
        parent::__construct(sprintf($this->message, $messageType));
    }
}
