<?php

declare(strict_types=1);

namespace SykesCottages\Qu\Message\Contract;

interface Message
{
    // @codingStandardsIgnoreStart
    /** @return mixed array */
    public function getBody(): array;
    // @codingStandardsIgnoreEnd
}
