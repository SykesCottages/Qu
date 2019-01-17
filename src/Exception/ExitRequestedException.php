<?php

declare(strict_types = 1);

namespace SykesCottages\Qu\Exception;

use Exception;

class ExitRequestedException extends Exception
{
    protected $message = "Exit has been requested for the queue: %s";

    public function __construct(string $queueName)
    {
        parent::__construct(sprintf($this->message, $queueName));
    }
}