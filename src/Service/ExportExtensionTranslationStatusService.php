<?php

declare(strict_types=1);

namespace App\Service;

use App\Configuration\Project;
use App\Crowdin\Repository\TranslationStatusRepository;
use App\File\PathResolver;
use App\Status\Project\StatusWriter;
use App\Utility\FileHandling;

final readonly class ExportExtensionTranslationStatusService
{
    public function __construct(
        private PathResolver $pathResolver,
        private StatusWriter $statusWriter,
        private TranslationStatusRepository $translationStatusRepository,
    ) {}

    public function export(Project $project): void
    {
        $translationProgresses = $this->translationStatusRepository->findByProjectId($project->id);
        if ($translationProgresses !== []) {
            $extensionKey = $project->extensionKey;

            $projectSubDir = $this->pathResolver->getRsyncPath() . sprintf('/%s/%s/%s-l10n/', $extensionKey[0], $extensionKey[1], $extensionKey);
            FileHandling::mkdir_deep($projectSubDir);

            $filePath = $projectSubDir . $extensionKey . '.json';
            $this->statusWriter->write($filePath, $translationProgresses);
        }
    }
}
