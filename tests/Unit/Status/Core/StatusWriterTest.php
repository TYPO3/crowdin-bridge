<?php

declare(strict_types=1);


namespace App\Tests\Unit\Status\Core;

use App\Crowdin\Dto\BranchTranslationProgress;
use App\Crowdin\Dto\DirectoryTranslationProgress;
use App\Crowdin\Dto\Language;
use App\Crowdin\Dto\TranslationProgress;
use App\Crowdin\Entity\Branch;
use App\Crowdin\Entity\Directory;
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

        $branchMain = new Branch(101, 1, 'main');
        $branch143 = new Branch(102, 1, '14.3');
        $branch134 = new Branch(103, 1, '13.4');

        $translationProgressesByBranch = [
            new BranchTranslationProgress(
                $branchMain,
                [
                    new TranslationProgress('de', 70, 30, 0, 0, 0),
                    new TranslationProgress('fr', 80, 40, 0, 0, 0),
                    new TranslationProgress('it', 90, 50, 0, 0, 0),
                ],
            ),
            new BranchTranslationProgress(
                $branch143,
                [
                    new TranslationProgress('de', 71, 51, 0, 0, 0),
                    new TranslationProgress('fr', 81, 61, 0, 0, 0),
                    new TranslationProgress('it', 91, 71, 0, 0, 0),
                ]
            ),
            new BranchTranslationProgress(
                $branch134,
                [
                    new TranslationProgress('de', 72, 52, 0, 0, 0),
                    new TranslationProgress('fr', 82, 62, 0, 0, 0),
                    new TranslationProgress('it', 92, 72, 0, 0, 0),
                ]
            ),
        ];

        $translationProgressesByDirectory = [
            new DirectoryTranslationProgress(
                $branchMain,
                new Directory(201, $branchMain->projectId, $branchMain->id, 301, 'adminpanel', '/main/sysext/typo3/adminpanel'),
                [
                    new TranslationProgress('de', 11, 1, 0, 0, 0),
                    new TranslationProgress('fr', 12, 2, 0, 0, 0),
                    new TranslationProgress('it', 13, 3, 0, 0, 0),
                ]
            ),
            new DirectoryTranslationProgress(
                $branchMain,
                new Directory(202, $branchMain->projectId, $branchMain->id, 302, 'backend', '/main/sysext/typo3/backend'),
                [
                    new TranslationProgress('de', 21, 7, 0, 0, 0),
                    new TranslationProgress('fr', 22, 8, 0, 0, 0),
                    new TranslationProgress('it', 23, 9, 0, 0, 0),
                ]
            ),
            new DirectoryTranslationProgress(
                $branch143,
                new Directory(203, $branch143->projectId, $branch143->id, 303, 'adminpanel', '/14.3/sysext/typo3/adminpanel'),
                [
                    new TranslationProgress('de', 31, 14, 0, 0, 0),
                    new TranslationProgress('fr', 32, 15, 0, 0, 0),
                    new TranslationProgress('it', 33, 16, 0, 0, 0),
                ]
            ),
            new DirectoryTranslationProgress(
                $branch143,
                new Directory(204, $branch143->projectId, $branch143->id, 304, 'adminpanel', '/14.3/sysext/typo3/adminpanel'),
                [
                    new TranslationProgress('de', 41, 17, 0, 0, 0),
                    new TranslationProgress('fr', 42, 18, 0, 0, 0),
                    new TranslationProgress('it', 43, 19, 0, 0, 0),
                ]
            ),
            new DirectoryTranslationProgress(
                $branch134,
                new Directory(205, $branch134->projectId, $branch134->id, 305, 'adminpanel', '/13.4/sysext/typo3/adminpanel'),
                [
                    new TranslationProgress('de', 51, 24, 0, 0, 0),
                    new TranslationProgress('fr', 52, 25, 0, 0, 0),
                    new TranslationProgress('it', 53, 26, 0, 0, 0),
                ]
            ),
            new DirectoryTranslationProgress(
                $branch134,
                new Directory(206, $branch134->projectId, $branch134->id, 306, 'adminpanel', '/13.4/sysext/typo3/adminpanel'),
                [
                    new TranslationProgress('de', 61, 27, 0, 0, 0),
                    new TranslationProgress('fr', 62, 28, 0, 0, 0),
                    new TranslationProgress('it', 63, 29, 0, 0, 0),
                ]
            ),
        ];

        $this->subject->write($filePath, $translationProgressesByBranch, $translationProgressesByDirectory);

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
                "de": 30,
                "fr": 40,
                "it": 50
            },
            "extensions": {
                "adminpanel": {
                    "translations": {
                        "de": 11,
                        "fr": 12,
                        "it": 13
                    },
                    "approvals": {
                        "de": 1,
                        "fr": 2,
                        "it": 3
                    }
                },
                "backend": {
                    "translations": {
                        "de": 21,
                        "fr": 22,
                        "it": 23
                    },
                    "approvals": {
                        "de": 7,
                        "fr": 8,
                        "it": 9
                    }
                }
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
            },
            "extensions": {
                "adminpanel": {
                    "translations": {
                        "de": 41,
                        "fr": 42,
                        "it": 43
                    },
                    "approvals": {
                        "de": 17,
                        "fr": 18,
                        "it": 19
                    }
                }
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
            },
            "extensions": {
                "adminpanel": {
                    "translations": {
                        "de": 61,
                        "fr": 62,
                        "it": 63
                    },
                    "approvals": {
                        "de": 27,
                        "fr": 28,
                        "it": 29
                    }
                }
            }
        }
    ]
}
EXPECTED_JSON,
            file_get_contents($filePath)
        );
    }
}
