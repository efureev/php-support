<?php

declare(strict_types=1);

namespace Php\Support\Tests;

use Php\Support\Helpers\Bit;
use PHPUnit\Framework\TestCase;

/**
 * Class BitTest
 */
final class BitTest extends TestCase
{
    public const LOGIN  = 1 << 0;
    public const READ   = 1 << 1;
    public const CREATE = 1 << 2;
    public const UPDATE = 1 << 3;
    public const DELETE = 1 << 4;

    /**
     * @return array
     */
    private static function permissions(): array
    {
        return [
            self::LOGIN,
            self::READ,
            self::CREATE,
            self::UPDATE,
            self::DELETE,
        ];
    }

    public function testGrant(): void
    {
        $sum = Bit::grant(self::permissions());
        static::assertEquals(31, $sum);

        $val =
            self::LOGIN |
            self::READ |
            self::CREATE |
            self::UPDATE |
            self::DELETE;

        static::assertEquals(31, $val);
        static::assertEquals(31, bindec(b'11111'));
    }

    public function testAddFlag(): void
    {
        $val = Bit::addFlag(0, self::LOGIN);
        static::assertEquals(1, $val);
        static::assertTrue(($val & self::LOGIN) > 0);
        static::assertTrue(Bit::checkFlag($val, self::LOGIN));

        $val = Bit::addFlag($val, self::READ);
        static::assertEquals(3, $val);
        static::assertTrue(($val & self::READ) > 0);
        static::assertTrue(Bit::checkFlag($val, self::READ));

        static::assertSame(($val & self::CREATE), 0);
        static::assertSame(($val & self::UPDATE), 0);
        static::assertSame(($val & self::DELETE), 0);
        static::assertFalse(Bit::checkFlag($val, self::CREATE));
        static::assertFalse(Bit::checkFlag($val, self::UPDATE));
        static::assertFalse(Bit::checkFlag($val, self::DELETE));
    }

    public function testRemoveFlag(): void
    {
        $val = Bit::removeFlag(0, self::CREATE);
        static::assertEquals(0, $val);

        $val = Bit::addFlag($val, self::LOGIN);
        static::assertTrue(Bit::checkFlag($val, self::LOGIN));

        $val1 = Bit::removeFlag($val, self::CREATE);
        static::assertEquals($val1, $val);

        $val = Bit::removeFlag($val1, self::LOGIN);
        static::assertEquals(0, $val);

        $val = Bit::addFlag(
            $val,
            self::READ | self::READ | self::DELETE
        );

        static::assertTrue(Bit::checkFlag($val, self::READ));
        static::assertTrue(Bit::checkFlag($val, self::DELETE));
        static::assertEquals(bindec(b'10010'), $val);

        $val = Bit::removeFlag($val, self::READ | self::DELETE);
        static::assertEquals(0, $val);
        foreach (self::permissions() as $permission) {
            static::assertFalse(Bit::checkFlag($val, $permission));
        }

        $val = Bit::addFlag(
            $val,
            self::READ | self::CREATE | self::DELETE
        );

        $val = Bit::removeFlag($val, self::READ);
        static::assertEquals(bindec(b'10100'), $val);
    }


    public function testExistFlag(): void
    {
        foreach (self::permissions() as $permission) {
            static::assertTrue(Bit::exist(self::permissions(), $permission));
        }

        for ($i = 5; $i <= 20; $i++) {
            static::assertFalse(Bit::exist(self::permissions(), 1 << $i));
        }
    }

    public function testDecBinPad(): void
    {
        $perms = [
            self::LOGIN  => '00001',
            self::READ   => '00010',
            self::CREATE => '00100',
            self::UPDATE => '01000',
            self::DELETE => '10000',
        ];

        $padLength = count($perms);

        foreach ($perms as $flag => $expected) {
            static::assertEquals($expected, Bit::decBinPad($flag, $padLength));
        }
    }
}
