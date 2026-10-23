<?php

declare(strict_types=1);

namespace App\Build;

use App\Configuration\Project;
use App\Configuration\ProjectCollection;
use App\Console\Output\OutputInterface;
use App\Crowdin\Repository\TranslationRepository;

readonly class Builder
{
    public function __construct(
        private ProjectCollection $projectCollection,
        private TranslationRepository $translationRepository,
    ) {}

    public function build(string $projectIdentifier, OutputInterface $output): void
    {
        if ($projectIdentifier !== '') {
            $this->exportSingleProject($projectIdentifier);
            return;
        }

        $errors = [];
        $output->start(count($this->projectCollection));
        foreach ($this->projectCollection as $project) {
            try {
                $message = $this->exportSingleProject($project->identifier);
                $output->advance($message);
            } catch (\Throwable $t) {
                $message = \sprintf('Project "%s" has an error: %s', $project->identifier, $t->getMessage());
                $errors[] = $message;
                $output->advance(\sprintf('<error>%s</error>', $message));
            }
        }
        $output->finish($errors);
    }

    private function exportSingleProject(string $projectIdentifier): string
    {
        $project = $this->projectCollection->findByIdentifier($projectIdentifier);
        if (!$project instanceof Project) {
            throw ProjectNotFoundException::fromProjectIdentifier($projectIdentifier);
        }

        $build = $this->translationRepository->buildProject($project->id);

        return $build->status->consoleMessage($projectIdentifier, $build->error);
    }
}
