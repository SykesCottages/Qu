<?php

declare(strict_types=1);

namespace SykesCottages\Qu\Message\Contract;

interface Message
{
    /** @return string[]|null */
    public function getBody(): ?array;
}
