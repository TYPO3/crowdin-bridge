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
/**
 * Triggers ProjectService to create setup file
 */
class SetupCommand extends Command
{
    public function __construct(protected ProjectService $projectService, ?string $name = null)
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Create `configuration.json` file');

        $projects = $this->projectService->updateConfiguration();
        sort($projects);
        $io->success(sprintf('%s projects have been configured!', count($projects)));
        $io->text(implode(', ', $projects));

        return Command::SUCCESS;
    }
}
