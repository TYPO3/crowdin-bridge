<?php

declare(strict_types=1);


namespace App\Tests\Unit\Crowdin\Repository;

use App\Crowdin\Dto\Language;
use App\Crowdin\Repository\LanguageRepository;
use App\File\PathResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(LanguageRepository::class)]
final class LanguageRepositoryTest extends TestCase
{
    private LanguageRepository $subject;

    protected function setUp(): void
    {
        $pathResolverStub = $this->createStub(PathResolver::class);
        $pathResolverStub
            ->method('getAssetsPath')
            ->willReturn(__DIR__ . '/Fixtures');

        $this->subject = new LanguageRepository($pathResolverStub);
    }

    #[Test]
    public function findByIdReturnsAvailableLanguage(): void
    {
        $actual = $this->subject->findById('cs');

        self::assertInstanceOf(Language::class, $actual);
        self::assertSame('cs', $actual->id);
        self::assertSame('Czech', $actual->name);
    }

    #[Test]
    public function findByIdReturnsNullOnNonExistingLanguage(): void
    {
        $actual = $this->subject->findById('xx');

        self::assertNull($actual);
    }

    #[Test]
    public function findByIdThrowsExceptionIfLanguageFileCannotBeRead(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1763660710);
        $this->expectExceptionMessageMatches('#The language file ".*/tests/Unit/Crowdin/Repository/Fixtures/NotAvailable/languages.json" cannot be read!#');

        $pathResolverStub = $this->createStub(PathResolver::class);
        $pathResolverStub
            ->method('getAssetsPath')
            ->willReturn(__DIR__ . '/Fixtures/NotAvailable');

        $subject = new LanguageRepository($pathResolverStub);
        $subject->findById('de');
    }
}
