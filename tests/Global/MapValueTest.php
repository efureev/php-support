<?php

declare(strict_types=1);

namespace Php\Support\Tests\Global;

use PHPUnit\Framework\TestCase;

final class MapValueTest extends TestCase
{
    /**
     * @test
     */
    public function mapValue(): void
    {
        $fnColl = static fn(string $value) => mb_strtoupper($value);
        $result = mapValue($fnColl, ['test', 'app']);
        $expect = [
            'TEST',
            'APP',
        ];
        self::assertEquals($expect, $result);
    }

    /**
     * @test
     */
    public function mapValueWithParams(): void
    {
        $fnColl = static fn(string $value, $key, string $prefix, string $suffix) =>
            $prefix . mb_strtoupper($value) . $suffix;
        $result = mapValue($fnColl, ['test', 'app'], '- ', '.');
        $expect = [
            '- TEST.',
            '- APP.',
        ];
        self::assertEquals($expect, $result);
    }
}
