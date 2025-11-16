<?php

declare(strict_types=1);

namespace App\Build;

use App\Configuration\ProjectCollection;
use App\Console\Output\OutputInterface;
use App\Repository\Crowdin\TranslationRepository;
use FriendsOfTYPO3\CrowdinBase\Configuration\Entity\Project;

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

        $status = $this->translationRepository->buildProject($project->id);

        $text = \sprintf('Project "%s"', $projectIdentifier);

        return match ($status) {
            'finished' => \sprintf('<comment>%s is already built</comment>', $text),
            'inProgress' => \sprintf('<info>%s has been built now</info>', $text),
            null => \sprintf('<error>%s has no clear status returned</error>', $text),
            default => \sprintf('<question>%s %s</question>', $text, $status),
        };
    }
}
