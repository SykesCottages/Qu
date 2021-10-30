<?php

declare(strict_types=1);

namespace SykesCottages\Qu\Consumer\Contract;

interface Consumable
{
    public function consume(callable $callback, callable $idleCallback): void;
}
