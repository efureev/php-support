<?php

declare(strict_types=1);

namespace Php\Support\Tests\Global;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EachValueTest extends TestCase
{
    #[Test]
    public function eachValue(): void
    {
        $result = [];
        $fnColl = static function (string $value) use (&$result) {
            $result[] = $value;
        };
        $expect = ['test', 'app'];
        eachValue($fnColl, $expect);

        self::assertEquals($expect, $result);
    }
}
