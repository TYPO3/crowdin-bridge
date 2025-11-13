<?php

declare(strict_types=1);

namespace App\Command\Status;

use App\Entity\BridgeConfiguration;
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
        private readonly BridgeConfiguration $bridgeConfiguration,
        private readonly ExportExtensionTranslationStatusService $translationStatusService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('extensionKey', InputArgument::OPTIONAL, 'Extension Key');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $extensionKey = (string)$input->getArgument('extensionKey');

        $projects = $this->bridgeConfiguration->getAllProjects();

        if ($extensionKey) {
            $this->exportProject($extensionKey, true, $io);
            return Command::SUCCESS;
        }

        $progressBar = new ProgressBar($output, count($projects));
        $progressBar->start();
        $verbose = $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
        foreach ($projects as $project) {
            if ($project->isCoreProject()) {
                $progressBar->advance();
                continue;
            }
            $this->exportProject($project->getExtensionKey(), $verbose, $io);
            $progressBar->advance();
        }
        $progressBar->finish();

        return Command::SUCCESS;
    }

    private function exportProject(string $extensionKey, bool $verbose, SymfonyStyle $io): void
    {
        if ($verbose) {
            $io->title(sprintf('Project %s', $extensionKey));
        }
        try {
            //             todo more output
            $this->translationStatusService->export($extensionKey);
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }
    }
}
