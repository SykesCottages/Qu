<?php

declare(strict_types=1);

namespace SykesCottages\Qu\Message\Contract;

interface Message
{
    /** @return array */
    public function getBody(): array;
}
