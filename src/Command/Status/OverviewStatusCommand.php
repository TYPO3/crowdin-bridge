<?php

declare(strict_types=1);

namespace App\Command\Status;

use App\Console\Output\ProgressBarOutput;
use App\Console\Output\ProjectIdentifierOutput;
use App\Status\Overview\StatusWriter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:status:overview',
    description: 'Status of all crowdin projects used for status page on translation server',
)]
final class OverviewStatusCommand extends Command
{
    public function __construct(
        private readonly StatusWriter $statusWriter,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Status of all projects');

        $verbose = $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
        $progressOutput = $verbose ? new ProjectIdentifierOutput($io) : new ProgressBarOutput(new ProgressBar($output), $io);

        try {
            $this->statusWriter->write($progressOutput);
        } catch (\Throwable $t) {
            $io->error($t->getMessage());
            return Command::FAILURE;
        }

        $io->info('Status has been exported!');
        return Command::SUCCESS;
    }
}
