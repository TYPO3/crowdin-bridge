<?php

declare(strict_types=1);

namespace App\Build;

use App\Entity\BridgeConfiguration;
use App\Service\ExportService;
use Symfony\Component\Console\Style\SymfonyStyle;

readonly class Builder
{
    public function __construct(
        private BridgeConfiguration $bridgeConfiguration,
        private ExportService $exportService,
    ) {}

    public function build(string $projectIdentifier, Progress $progress, SymfonyStyle $io, bool $verbose): true
    {
        $projects = $this->bridgeConfiguration->getAllProjects();
        if ($projectIdentifier !== '') {
            if (!isset($projects[$projectIdentifier])) {
                throw ProjectNotFoundException::fromProjectIdentifier($projectIdentifier);
            }

            $this->exportSingleProject($projectIdentifier, true, $io);

            return true;
        }

        ($progress->start)(count($projects));
        foreach ($projects as $project) {
            $this->exportSingleProject($project->getCrowdinIdentifier(), $verbose, $io);
            ($progress->advance)();
        }
        ($progress->finish)();

        return true;
    }

    private function exportSingleProject(string $projectIdentifier, bool $verbose, SymfonyStyle $io): void
    {
        try {
            $response = $this->exportService->export($projectIdentifier);
            $text = sprintf('Trigger build of project "%s"', $projectIdentifier);
            $status = 'comment';
            if ($response) {
                if ($response->getStatus() === 'finished' && $response->getProgress() === 100) {
                    $status = 'info';
                }
                $text .= sprintf(' with progress "%s": %s%%.', $response->getStatus(), $response->getProgress());
            }
            if ($verbose) {
                if ($status === 'info') {
                    $io->info($text);
                } else {
                    $io->comment($text);
                }
            }
        } catch (\Exception $e) {
            $io->error(sprintf('ERROR triggering build of "%s": %s', $projectIdentifier, $e->getMessage()));
        }
    }

}
