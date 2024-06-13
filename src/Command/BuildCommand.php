<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\BridgeConfiguration;
use App\Service\ExportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:build',
    description: 'Trigger build of a project',
    hidden: false
)]
class BuildCommand extends Command
{
    public function __construct(
        protected readonly BridgeConfiguration $bridgeConfiguration,
        protected readonly ExportService $exportService,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->addArgument('project', InputArgument::OPTIONAL, 'Project identifier');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $projectIdentifier = $input->getArgument('project');
        $projects = $this->bridgeConfiguration->getAllProjects();

        if ($projectIdentifier) {
            if (!isset($projects[$projectIdentifier])) {
                $io->error(sprintf('Project "%s" does not exist', $projectIdentifier));
                return Command::FAILURE;
            }
            $this->exportSingleProject($projectIdentifier, true, $io);
            return Command::SUCCESS;
        }

        $verbose = $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
        $progressBar = new ProgressBar($output, count($projects));
        $progressBar->start();

        foreach ($projects as $project) {
            $this->exportSingleProject($project->getCrowdinIdentifier(), $verbose, $io);
            $progressBar->advance();
        }
        $progressBar->finish();
        return Command::SUCCESS;
    }

    protected function exportSingleProject(string $projectIdentifier, bool $verbose, SymfonyStyle $io): void
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
