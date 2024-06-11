<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Management\ProjectService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:setup',
    description: 'Create configuration file',
    hidden: false
)]
class SetupCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Create `configuration.json` file');

        $service = new ProjectService();
        $projects = $service->updateConfiguration();
        sort($projects);
        $io->success(sprintf('%s projects have been configured!', count($projects)));
        $io->text(implode(', ', $projects));

        return 0;
    }
}
