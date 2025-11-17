<?php

declare(strict_types=1);

namespace App\Command\Status;

use App\Status\Overview\StatusWriter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
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

        $this->statusWriter->write();

        $io->info('Status has been exported!');
        return Command::SUCCESS;
    }
}
