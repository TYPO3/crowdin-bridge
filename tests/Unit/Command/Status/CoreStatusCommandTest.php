<?php

declare(strict_types=1);


namespace App\Tests\Unit\Command\Status;

use App\Command\Status\CoreStatusCommand;
use App\Configuration\Project;
use App\Configuration\ProjectCollection;
use App\Status\Core\TranslationStatusExporter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(CoreStatusCommand::class)]
final class CoreStatusCommandTest extends TestCase
{
    #[Test]
    public function executeRunsSuccessfully(): void
    {
        $coreProject = new Project(1, 'typo3-cms', 'typo3', []);
        $projectCollection = new ProjectCollection($coreProject);

        $translationStatusExporterMock = $this->createMock(TranslationStatusExporter::class);

        $command = new CoreStatusCommand($projectCollection, $translationStatusExporterMock);
        $commandTester = new CommandTester($command);

        $translationStatusExporterMock
            ->expects(self::once())
            ->method('exportProject')
            ->with($coreProject, self::anything());

        $commandTester->execute([]);

        self::assertStringContainsString('[OK] Core status has been exported', $commandTester->getDisplay());
        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());
    }

    #[Test]
    public function executeReturnsFailureIfCoreProjectIsNotAvailable(): void
    {
        $projectCollection = new ProjectCollection();

        $translationStatusExporterMock = $this->createMock(TranslationStatusExporter::class);

        $command = new CoreStatusCommand($projectCollection, $translationStatusExporterMock);
        $commandTester = new CommandTester($command);

        $translationStatusExporterMock
            ->expects(self::never())
            ->method('exportProject');

        $commandTester->execute([]);

        self::assertStringContainsString('[ERROR] Project with identifier "typo3-cms" not found', $commandTester->getDisplay());
        self::assertSame(Command::FAILURE, $commandTester->getStatusCode());
    }

    #[Test]
    public function executeReturnsFailureIfAnErrorOccurredOnExportingProject(): void
    {
        $coreProject = new Project(1, 'typo3-cms', 'typo3', []);
        $projectCollection = new ProjectCollection($coreProject);

        $translationStatusExporterStub = self::createStub(TranslationStatusExporter::class);

        $command = new CoreStatusCommand($projectCollection, $translationStatusExporterStub);
        $commandTester = new CommandTester($command);

        $translationStatusExporterStub
            ->method('exportProject')
            ->willThrowException(new \Exception('Some error'));

        $commandTester->execute([]);

        self::assertStringContainsString('[ERROR] An error occurred: Some error', $commandTester->getDisplay());
        self::assertSame(Command::FAILURE, $commandTester->getStatusCode());
    }
}
