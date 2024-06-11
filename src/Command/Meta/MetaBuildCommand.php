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
    name: 'app:meta:build',
    description: 'Build all projects by running the "crowdin:build" command for *all* projects.',
    hidden: false
)]
class MetaBuildCommand extends Command
{
    public function __construct(
        protected readonly BridgeConfiguration $bridgeConfiguration,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $command = $this->getApplication()->find('app:build');

        $projects = $this->bridgeConfiguration->getAllProjects();
        $progressBar = new ProgressBar($output, count($projects));
        $progressBar->start();

        foreach ($projects as $project) {
            $arguments = [
                'command' => 'app:build',
                'project' => $project->getCrowdinIdentifier(),
            ];
            $input = new ArrayInput($arguments);
            $command->run($input, $output);
            $progressBar->advance();
        }
        $progressBar->finish();

        return 0;
    }
}
