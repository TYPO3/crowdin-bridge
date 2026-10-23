<?php

declare(strict_types=1);


namespace App\Tests\Unit\Status\Core;

use App\Configuration\Project;
use App\Console\Output\SimpleOutput;
use App\Crowdin\Dto\Language;
use App\Crowdin\Dto\TranslationProgress;
use App\Crowdin\Entity\Branch;
use App\Crowdin\Entity\Directory;
use App\Crowdin\Repository\BranchRepository;
use App\Crowdin\Repository\DirectoryRepository;
use App\Crowdin\Repository\LanguageRepository;
use App\Crowdin\Repository\TranslationStatusRepository;
use App\File\PathResolver;
use App\Status\Core\StatusWriter;
use App\Status\Core\TranslationStatusExporter;
use Lcobucci\Clock\FrozenClock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Style\SymfonyStyle;

#[CoversClass(TranslationStatusExporter::class)]
final class TranslationStatusExporterTest extends TestCase
{
    private string $frontendPath;
    private BranchRepository&Stub $branchRepositoryStub;
    private DirectoryRepository&Stub $directoryRepositoryStub;
    private PathResolver&Stub $pathResolverStub;
    private TranslationStatusRepository&Stub $translationStatusRepositoryStub;
    private TranslationStatusExporter $translationStatusExporter;

    protected function setUp(): void
    {
        $this->branchRepositoryStub = self::createStub(BranchRepository::class);

        $this->directoryRepositoryStub = self::createStub(DirectoryRepository::class);

        $this->frontendPath = sys_get_temp_dir() . '/crowdin-bridge-translations-status-export-test-' . uniqid();
        mkdir($this->frontendPath);
        $this->pathResolverStub = self::createStub(PathResolver::class);
        $this->pathResolverStub
            ->method('getFrontendPath')
            ->willReturn($this->frontendPath);

        $languageRepositoryStub = self::createStub(LanguageRepository::class);
        $languageRepositoryStub
            ->method('findById')
            ->willReturnMap([
                ['de', new Language('de', 'German')],
                ['fr', new Language('fr', 'French')],
                ['it', new Language('it', 'Italian')],
            ]);

        $statusWriter = new StatusWriter(
            new FrozenClock(new \DateTimeImmutable('2026-10-21 11:00:00')),
            $languageRepositoryStub,
        );

        $this->translationStatusRepositoryStub = $this->createStub(TranslationStatusRepository::class);

        $this->translationStatusExporter = new TranslationStatusExporter(
            $this->branchRepositoryStub,
            $this->directoryRepositoryStub,
            $this->pathResolverStub,
            $statusWriter,
            $this->translationStatusRepositoryStub,
        );
    }

    #[Test]
    public function exportProjectCallsStatusWriterCorrectly(): void
    {
        $coreProject = new Project(1, 'typo3-cms', 'typo3', []);

        $this->branchRepositoryStub
            ->method('list')
            ->willReturn([
                new Branch(101, 1, 'main'),
                new Branch(102, 1, '14.3'),
                new Branch(103, 1, '13.4'),
            ]);

        $this->directoryRepositoryStub
            ->method('findByFilter')
            ->willReturn([
                new Directory(201, 1, 101, 301, 'adminpanel', '/main/sysext/typo3/adminpanel'),
                new Directory(202, 1, 101, 302, 'backend', '/main/sysext/typo3/backend'),
                new Directory(203, 1, 102, 303, 'core', '/14.3/sysext/typo3/core'),
                new Directory(204, 1, 102, 304, 'fluid', '/14.3/sysext/typo3/fluid'),
                new Directory(205, 1, 103, 305, 'reactions', '/13.4/sysext/typo3/reactions'),
                new Directory(206, 1, 103, 306, 'seo', '/13.4/sysext/typo3/seo'),
                new Directory(207, 1, 104, 307, 'webhooks', '/12.4/sysext/typo3/webhooks'),
                new Directory(208, 1, 104, 308, 'workspaces', '/12.4/sysext/typo3/workspaces'),
            ]);

        $this->translationStatusRepositoryStub
            ->method('findByBranchId')
            ->willReturnCallback(static function(int $projectId, int $branchId): array {
                if ($projectId === 1) {
                    return match($branchId) {
                        101 => [
                            new TranslationProgress('de', 70, 50, 0, 0, 0),
                            new TranslationProgress('fr', 80, 60, 0, 0, 0),
                            new TranslationProgress('it', 90, 70, 0, 0, 0),
                        ],
                        102 => [
                            new TranslationProgress('de', 71, 51, 0, 0, 0),
                            new TranslationProgress('fr', 81, 61, 0, 0, 0),
                            new TranslationProgress('it', 91, 71, 0, 0, 0),
                        ],
                        103 => [
                            new TranslationProgress('de', 72, 52, 0, 0, 0),
                            new TranslationProgress('fr', 82, 62, 0, 0, 0),
                            new TranslationProgress('it', 92, 72, 0, 0, 0),
                        ],
                    };
                }

                throw new \Exception('Should not happen!');
            });

        $this->translationStatusExporter->exportProject($coreProject, new SimpleOutput(self::createStub(SymfonyStyle::class)));

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
            },
            "extensions": {
                "adminpanel": {
                    "approvals": [],
                    "translations": []
                },
                "backend": {
                    "approvals": [],
                    "translations": []
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
                "core": {
                    "approvals": [],
                    "translations": []
                },
                "fluid": {
                    "approvals": [],
                    "translations": []
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
                "reactions": {
                    "approvals": [],
                    "translations": []
                },
                "seo": {
                    "approvals": [],
                    "translations": []
                }
            }
        }
    ]
}
EXPECTED_JSON,
            file_get_contents($this->frontendPath . '/status_core.json')
        );
    }
}
