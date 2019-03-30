<?php

declare(strict_types=1);

namespace Php\Support\Interfaces;

/**
 * Interface Command
 *
 * @package Php\Support\Interfaces
 */
interface Command
{
    public function execute(): void;
}
