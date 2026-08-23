<?php

declare(strict_types=1);

namespace Php\Support\Tests\Helpers;

use Php\Support\Helpers\URLify;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class URLifyTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function transliterations(): array
    {
        return [
            'latin accents'   => [
                'Àéîõü',
                'Aeiou',
            ],
            'cyrillic'        => [
                'Привет мир',
                'Privet mir',
            ],
            'greek'           => [
                'Ελλάδα',
                'Ellada',
            ],
            'ligatures'       => [
                'Æther æon',
                'AEther aeon',
            ],
            'ascii untouched' => [
                'plain ascii 123',
                'plain ascii 123',
            ],
            'empty'           => [
                '',
                '',
            ],
        ];
    }

    #[DataProvider('transliterations')]
    public function testDowncode(string $input, string $expected): void
    {
        self::assertSame($expected, URLify::downcode($input));
    }

    #[Test]
    public function germanMapTakesPriorityWhenRequested(): void
    {
        // the shared latin map turns ü into u; the German one into ue
        self::assertSame('ue', URLify::downcode('ü', 'de'));
        self::assertSame('ss', URLify::downcode('ß', 'de'));
    }

    #[Test]
    public function aLanguageMapDoesNotLeakIntoLaterCalls(): void
    {
        // regression: initLanguageMap() used to promote the requested language inside the shared
        // $maps array permanently, and the cache guard accepted "no language" as a cache hit,
        // so one call with 'de' made ü decode as "ue" for the rest of the process
        self::assertSame('u', URLify::downcode('ü'));
        self::assertSame('ue', URLify::downcode('ü', 'de'));
        self::assertSame('u', URLify::downcode('ü'));
        self::assertSame('ue', URLify::downcode('ü', 'de'));
        self::assertSame('u', URLify::downcode('ü', 'latin'));
        self::assertSame('u', URLify::downcode('ü'));
    }

    #[Test]
    public function oeLigatureIsNotCoveredByTheBundledMaps(): void
    {
        // Œ/œ are simply missing from the vendored tables - documented here so the gap is
        // visible rather than surprising. Goes away when URLify is replaced (audit L-8).
        self::assertSame('œuvre', URLify::downcode('œuvre'));
    }

    #[Test]
    public function seemsUTF8(): void
    {
        self::assertTrue(URLify::seemsUTF8('plain'));
        self::assertTrue(URLify::seemsUTF8('Привет'));
        self::assertTrue(URLify::seemsUTF8(''));
        self::assertFalse(URLify::seemsUTF8("\xC3\x28"));
        self::assertFalse(URLify::seemsUTF8("\xFF\xFE"));
    }

    #[Test]
    public function nonUtf8InputFallsBackToIso8859(): void
    {
        // "Ünïcödé" encoded as ISO-8859-1, which is not valid UTF-8
        $iso = "\xDCn\xEFc\xF6d\xE9";

        self::assertFalse(URLify::seemsUTF8($iso));
        self::assertSame('Unicode', URLify::downcode($iso));
    }
}
