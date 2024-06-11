<?php

declare(strict_types=1);

namespace App\Command\Meta;

use App\Entity\BridgeConfiguration;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:meta:status:export',
    description: 'Meta :: Export status of projects',
    hidden: false
)]
class MetaStatusExportCommand extends Command
{
    public function __construct(
        protected readonly BridgeConfiguration $bridgeConfiguration,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $command = $this->getApplication()->find('app:status:export');

        $projects = $this->bridgeConfiguration->getAllProjects();
        $progressBar = new ProgressBar($output, count($projects));
        $progressBar->start();

        foreach ($projects as $project) {
            if ($project->isCoreProject()) {
                $progressBar->advance();
                continue;
            }
            $arguments = [
                'command' => 'app:status:export',
                'extensionKey' => $project->getExtensionKey(),
            ];
            $input = new ArrayInput($arguments);
            $command->run($input, $output);
            $progressBar->advance();
        }
        $progressBar->finish();

        return 0;
    }
}
