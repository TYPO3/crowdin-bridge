<?php

declare(strict_types=1);

namespace App\Command\Status;

use App\Configuration\Project;
use App\Configuration\ProjectCollection;
use App\Service\ExportExtensionTranslationStatusService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:status:project',
    description: 'Export extension translation status',
)]
final class ProjectStatusCommand extends Command
{
    public function __construct(
        private readonly ProjectCollection $projectCollection,
        private readonly ExportExtensionTranslationStatusService $translationStatusService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('project', InputArgument::OPTIONAL, 'Project identifier');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $projectIdentifier = (string)$input->getArgument('project');

        if ($projectIdentifier !== '') {
            $project = $this->projectCollection->findByIdentifier($projectIdentifier);
            if (!$project instanceof Project) {
                $io->error(\sprintf('Project with identifier "%s" not found', $projectIdentifier));
                return Command::FAILURE;
            }

            $this->exportProject($project, true, $io);
            return Command::SUCCESS;
        }

        $progressBar = new ProgressBar($output, count($this->projectCollection));
        $progressBar->start();
        $verbose = $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
        foreach ($this->projectCollection as $project) {
            if ($project->isCoreProject()) {
                $progressBar->advance();
                continue;
            }
            $this->exportProject($project, $verbose, $io);
            $progressBar->advance();
        }
        $progressBar->finish();

        return Command::SUCCESS;
    }

    private function exportProject(Project $project, bool $verbose, SymfonyStyle $io): void
    {
        if ($verbose) {
            $io->title(sprintf('Project %s', $project->extensionKey));
        }
        try {
            //             todo more output
            $this->translationStatusService->export($project);
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }
    }
}
