<?php

declare(strict_types=1);


namespace App\Tests\Status\Project;

use App\Crowdin\Dto\Language;
use App\Crowdin\Dto\TranslationProgress;
use App\Crowdin\Repository\LanguageRepository;
use App\Status\Project\StatusWriter;
use App\Status\StatusProcessingException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(StatusWriter::class)]
final class StatusWriterTest extends TestCase
{
    private StatusWriter $subject;

    protected function setUp(): void
    {
        $languageRepositoryStub = $this->createStub(LanguageRepository::class);
        $languageRepositoryStub
            ->method('findById')
            ->willReturnMap([
                ['de', new Language('de', 'German')],
                ['es-ES', new Language('es-ES', 'Spanish')],
                ['tpi', null],
            ]);

        $this->subject = new StatusWriter($languageRepositoryStub);
    }

    #[Test]
    public function writeWritesStatusFileCorrectly(): void
    {
        $translationProgresses = [
            new TranslationProgress('de', 85,42,50, 40, 30),
            new TranslationProgress('tpi', 43, 28, 51, 41, 31),
            new TranslationProgress('es-ES', 44, 29, 52, 42, 32),
        ];

        $path = '/tmp/crowdin-bridge-status-writer-test-' . uniqid();
        mkdir($path);

        $filePath = $path . '/status.json';
        $this->subject->write($filePath, $translationProgresses);

        self::assertFileExists($filePath);
        self::assertJsonFileEqualsJsonFile(__DIR__ . '/Expected/statuswriter.json', $filePath);
    }

    #[Test]
    public function writeThrowsExceptionIfStatusFileCannotBeWritten(): void
    {
        $this->expectException(StatusProcessingException::class);
        $this->expectExceptionCode(1763293453);

        $translationProgresses = [
            new TranslationProgress('de', 85,42,50, 40, 30),
            new TranslationProgress('tpi', 43, 28, 51, 41, 31),
        ];

        $path = '/tmp/crowdin-bridge-status-writer-test-' . uniqid();

        $filePath = $path . '/status.json';
        $this->subject->write($filePath, $translationProgresses);
    }
}
