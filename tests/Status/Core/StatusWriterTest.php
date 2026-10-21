<?php

declare(strict_types=1);


namespace App\Tests\Status\Core;

use App\Crowdin\Dto\BranchTranslationProgress;
use App\Crowdin\Dto\Language;
use App\Crowdin\Dto\TranslationProgress;
use App\Crowdin\Entity\Branch;
use App\Crowdin\Repository\LanguageRepository;
use App\Status\Core\StatusWriter;
use Lcobucci\Clock\FrozenClock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(StatusWriter::class)]
final class StatusWriterTest extends TestCase
{
    private StatusWriter $subject;

    protected function setUp(): void
    {
        $languageRepositoryStub = self::createStub(LanguageRepository::class);
        $languageRepositoryStub
            ->method('findById')
            ->willReturnMap([
                ['de', new Language('de', 'German')],
                ['fr', new Language('fr', 'French')],
                ['it', new Language('it', 'Italian')],
            ]);

        $this->subject = new StatusWriter(
            new FrozenClock(new \DateTimeImmutable('2026-10-21 11:00:00')),
            $languageRepositoryStub,
        );
    }

    #[Test]
    public function writeStoresStatusCorrectly(): void
    {
        $filePath = sys_get_temp_dir() . '/crowdin-bridge-status-writer-' . uniqid() . '/status_core.json';
        mkdir(dirname($filePath));

        $translationProgressesByBranch = [
            new BranchTranslationProgress(
                new Branch(101, 1, 'main'),
                [
                    new TranslationProgress('de', 70, 50, 0, 0, 0),
                    new TranslationProgress('fr', 80, 60, 0, 0, 0),
                    new TranslationProgress('it', 90, 70, 0, 0, 0),
                ],
            ),
            new BranchTranslationProgress(
                new Branch(102, 1, '14.3'),
                [
                    new TranslationProgress('de', 71, 51, 0, 0, 0),
                    new TranslationProgress('fr', 81, 61, 0, 0, 0),
                    new TranslationProgress('it', 91, 71, 0, 0, 0),
                ]
            ),
            new BranchTranslationProgress(
                new Branch(103, 1, '13.4'),
                [
                    new TranslationProgress('de', 72, 52, 0, 0, 0),
                    new TranslationProgress('fr', 82, 62, 0, 0, 0),
                    new TranslationProgress('it', 92, 72, 0, 0, 0),
                ]
            ),
        ];

        $this->subject->write($filePath, $translationProgressesByBranch);

        self::assertJsonStringEqualsJsonString(
            <<<EXPECTED_JSON
{
    "date": "2026-10-21T11:00:00+00:00",
    "languages": {
        "de": "German",
        "fr": "French",
        "it": "Italian"
    },
    "branches": [
        {
            "branch": "main",
            "translations": {
                "de": 70,
                "fr": 80,
                "it": 90
            },
            "approvals": {
                "de": 50,
                "fr": 60,
                "it": 70
            }
        },
        {
            "branch": "14.3",
            "translations": {
                "de": 71,
                "fr": 81,
                "it": 91
            },
            "approvals": {
                "de": 51,
                "fr": 61,
                "it": 71
            }
        },
        {
            "branch": "13.4",
            "translations": {
                "de": 72,
                "fr": 82,
                "it": 92
            },
            "approvals": {
                "de": 52,
                "fr": 62,
                "it": 72
            }
        }
    ]
}
EXPECTED_JSON,
            file_get_contents($filePath)
        );
    }
}
