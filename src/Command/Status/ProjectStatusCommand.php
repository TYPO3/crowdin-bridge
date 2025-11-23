<?php

declare(strict_types=1);

namespace App\Command\Status;

use App\Configuration\Project;
use App\Configuration\ProjectCollection;
use App\Console\Output\ProgressBarOutput;
use App\Console\Output\ProjectIdentifierOutput;
use App\Status\Project\TranslationStatusExporter;
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
        private readonly TranslationStatusExporter $translationStatusExporter,
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

            $message = $this->translationStatusExporter->exportProject($project);
            $io->success($message);
            return Command::SUCCESS;
        }

        $verbose = $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
        $progressOutput = $verbose ? new ProjectIdentifierOutput($io) : new ProgressBarOutput(new ProgressBar($output), $io);

        try {
            $this->translationStatusExporter->exportProjects($progressOutput);
        } catch (\Throwable $t) {
            $io->error($t->getMessage());
            return Command::FAILURE;
        }

        $io->success('Projects have been exported');
        return Command::SUCCESS;
    }
}
