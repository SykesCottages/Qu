<?php

declare(strict_types=1);

namespace SykesCottages\Qu\Exception;

use Exception;
use function sprintf;

class ExitRequestedException extends Exception
{
    /** @var string */
    protected $message = 'Exit has been requested for the queue: %s';

    public function __construct(string $queueName)
    {
        parent::__construct(sprintf($this->message, $queueName));
    }
}
