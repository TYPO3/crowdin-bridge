<?php

declare(strict_types=1);

namespace App\Build;

use App\Configuration\ProjectCollection;
use App\Console\Output\OutputInterface;
use App\Service\ExportService;

readonly class Builder
{
    public function __construct(
        private ProjectCollection $projectCollection,
        private ExportService $exportService,
    ) {}

    public function build(string $projectIdentifier, OutputInterface $output): void
    {
        if ($projectIdentifier !== '') {
            if (!$this->projectCollection->findByIdentifier($projectIdentifier)) {
                throw ProjectNotFoundException::fromProjectIdentifier($projectIdentifier);
            }

            $this->exportSingleProject($projectIdentifier);

            return;
        }

        $errors = [];
        $output->start(count($this->projectCollection));
        foreach ($this->projectCollection as $project) {
            try {
                $result = $this->exportSingleProject($project->identifier);
                $output->advance($result);
            } catch (\Throwable $t) {
                $errors[] = $t->getMessage();
                $output->advance(\sprintf('<error>Project "%s" has an error: %s</error>', $project->identifier, $t->getMessage()));
            }
        }
        $output->finish($errors);
    }

    private function exportSingleProject(string $projectIdentifier): string
    {
        $response = $this->exportService->export($projectIdentifier);

        $text = \sprintf('Project "%s"', $projectIdentifier);

        return match ($response?->getStatus()) {
            'finished' => \sprintf('<comment>%s is already built</comment>', $text),
            'inProgress' => \sprintf('<info>%s has been built now</info>', $text),
            null => \sprintf('<error>%s has no clear status returned</error>', $text),
            default => \sprintf('<question>%s %s</question>', $text, $response->getStatus()),
        };
    }
}
