<?php

declare(strict_types=1);

namespace Php\Support\Tests\Enums\data;

use Php\Support\Enums\WithEnhances;

enum IntEnum: int
{
    use WithEnhances;

    case ONE = 1;

    case TWO = 2;

    case THREE = 3;
}
