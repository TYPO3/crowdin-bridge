<?php

declare(strict_types=1);


namespace App\Tests\Status\Overview;

use App\Configuration\Language;
use App\Configuration\Project;
use App\Configuration\ProjectCollection;
use App\Console\Output\OutputInterface;
use App\Crowdin\Dto\TranslationProgress;
use App\Crowdin\Repository\TranslationStatusRepository;
use App\File\PathResolver;
use App\Status\Overview\JsonStatusWriter;
use App\Status\Overview\PageStatusWriter;
use App\Status\Overview\StatusWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(StatusWriter::class)]
final class StatusWriterTest extends TestCase
{
    #[Test]
    public function writeWritesJsonCorrectly(): void
    {
        $rsyncPath = '/tmp/crowdin-bridge-status-writer-test-' . uniqid();
        mkdir($rsyncPath);

        $pathResolverStub = $this->createStub(PathResolver::class);
        $pathResolverStub
            ->method('getRsyncPath')
            ->willReturn($rsyncPath);
        $jsonStatusWriter = new JsonStatusWriter($pathResolverStub);
        $pageStatusWriterStub = $this->createStub(PageStatusWriter::class);

        $project1 = new Project(
            1,
            'typo3-cms',
            'typo3-cms',
            [
                new Language('ro', 'Romanian'),
                new Language('de', 'German'),
                new Language('it', 'Italian'),
            ]
        );
        $project2 = new Project(
            2,
            'typo3-extension-some-project',
            'some_project',
            [
                new Language('de', 'German'),
                new Language('fr', 'French'),
            ]
        );
        $project3 = new Project(
            3,
            'typo3-extension-another-project',
            'some_project',
            [
                new Language('ro', 'Romanian'),
                new Language('it', 'Italian'),
            ]
        );
        $project4 = new Project(
            4,
            'typo3-extension-nonusable-project',
            'nonusable_project',
            [
                new Language('de', 'German')
            ]
        );
        $project5 = new Project(
            5,
            'typo3-extension-project-without-approvals',
            'project_without_approvals',
            [
                new Language('de', 'German')
            ]
        );
        $projectCollection = new ProjectCollection($project1, $project2, $project3, $project4, $project5);

        $translationStatusRepositoryStub = $this->createStub(TranslationStatusRepository::class);
        $translationStatusRepositoryStub
            ->method('findByProjectId')
            ->willReturnMap([
                [
                    1,
                    [
                        new TranslationProgress('de', 85, 85),
                        new TranslationProgress('it', 33, 22),
                        new TranslationProgress('ro', 48, 41),
                    ]
                ],
                [
                    2,
                    [
                        new TranslationProgress('de', 48, 42),
                        new TranslationProgress('fr', 12, 3),
                    ]
                ],
                [
                    3,
                    [
                        new TranslationProgress('it', 100, 72),
                        new TranslationProgress('ro', 27, 27),
                    ]
                ],
                [
                    4,
                    [
                        new TranslationProgress('de', 0, 0),
                    ]
                ],
                [
                5,
                    [
                        new TranslationProgress('de', 3, 0),
                    ]
                ]
            ]);

        $outputDummy = new class implements OutputInterface {
            public function start(int $max): void
            {
                // do nothing
            }

            public function advance(string $text): void
            {
                // do nothing
            }

            public function finish(array $errors): void
            {
                // do nothing
            }
        };

        $subject = new StatusWriter($jsonStatusWriter, $pageStatusWriterStub, $projectCollection, $translationStatusRepositoryStub);
        $subject->write($outputDummy);

        self::assertJsonFileEqualsJsonFile(__DIR__ . '/Expected/statuswriter.json', $rsyncPath . '/status.json');
    }
}
