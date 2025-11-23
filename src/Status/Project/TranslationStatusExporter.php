<?php

declare(strict_types=1);

namespace App\Status\Project;

use App\Configuration\Project;
use App\Configuration\ProjectCollection;
use App\Console\Output\OutputInterface;
use App\Crowdin\Repository\TranslationStatusRepository;
use App\File\PathResolver;
use App\Utility\FileHandling;

final readonly class TranslationStatusExporter
{
    public function __construct(
        private PathResolver $pathResolver,
        private ProjectCollection $projectCollection,
        private StatusWriter $statusWriter,
        private TranslationStatusRepository $translationStatusRepository,
    ) {}

    public function exportProjects(OutputInterface $output): void
    {
        $output->start(count($this->projectCollection));
        $errors = [];
        foreach ($this->projectCollection as $project) {
            if ($project->isCoreProject()) {
                $output->advance('Project "typo3-core" has been skipped as it is not an extension');
                continue;
            }

            try {
                $message = $this->exportProject($project);
                $output->advance($message);
            } catch (\Throwable $t) {
                $message = \sprintf('Project "%s" has an error: %s', $project->identifier, $t->getMessage());
                $errors[] = $message;
                $output->advance(\sprintf('<error>%s</error>', $message));
            }
        }
        $output->finish($errors);
    }

    public function exportProject(Project $project): string
    {
        $translationProgresses = $this->translationStatusRepository->findByProjectId($project->id);
        if ($translationProgresses === []) {
            return \sprintf('Project "%s" has no translations', $project->identifier);
        }

        $extensionKey = $project->extensionKey;

        $projectSubDir = $this->pathResolver->getRsyncPath() . sprintf('/%s/%s/%s-l10n/', $extensionKey[0], $extensionKey[1], $extensionKey);
        FileHandling::mkdir_deep($projectSubDir);

        $filePath = $projectSubDir . $extensionKey . '.json';
        $this->statusWriter->write($filePath, $translationProgresses);

        return \sprintf('Project "%s" has been exported successfully', $project->identifier);
    }
}
