<?php

declare(strict_types=1);

namespace App\Command;

use App\Build\Builder;
use App\Console\Output\ProgressBarOutput;
use App\Console\Output\ProjectIdentifierOutput;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:build',
    description: 'Trigger build of one or all projects',
)]
final class BuildCommand extends Command
{
    public function __construct(
        private readonly Builder $builder,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('project', InputArgument::OPTIONAL, 'Project identifier', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $projectIdentifier = $input->getArgument('project');
        $verbose = $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
        $progressOutput = $verbose ? new ProjectIdentifierOutput($io) : new ProgressBarOutput(new ProgressBar($output), $io);

        try {
            $this->builder->build($projectIdentifier, $progressOutput);
        } catch (\Throwable $t) {
            $io->error($t->getMessage());
            return Command::FAILURE;
        }

        if ($projectIdentifier === '') {
            $io->success('Projects have been built');
        } else {
            $io->success(\sprintf('Project "%s" has been built', $projectIdentifier));
        }
        return Command::SUCCESS;
    }
}
