<?php

declare(strict_types=1);


namespace App\Tests\Info;

use App\Info\LanguageInformation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(LanguageInformation::class)]
final class LanguageInformationTest extends TestCase
{
    #[Test]
    #[DataProvider('provider')]
    public function getLanguageForTypo3(string $language, string $expected): void
    {
        self::assertSame($expected, LanguageInformation::getLanguageForTypo3($language));
    }

    public static function provider(): iterable
    {
        yield 'with es-ES' => [
            'language' => 'es-ES',
            'expected' => 'es',
        ];

        yield 'with de' => [
            'language' => 'de',
            'expected' => 'de',
        ];
    }
}
