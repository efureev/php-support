<?php

declare(strict_types=1);

namespace Php\Support\Tests\Enums\data;

use Php\Support\Enums\WithEnhancesForStrings;

enum StringsEnum: string
{
    use WithEnhancesForStrings;

    case SHORT = 'short';

    case LONG = 'long';

    case EMPTY = 'empty';
}
