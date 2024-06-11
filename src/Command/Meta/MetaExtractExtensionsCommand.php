<?php
declare(strict_types=1);

namespace App\Command\Meta;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\BridgeConfiguration;

#[AsCommand(
    name: 'app:meta:extractExtensions',
    description: 'Download & process translations of all extensions',
    hidden: false
)]
class MetaExtractExtensionsCommand extends Command
{
    public function __construct(
        protected readonly BridgeConfiguration $bridgeConfiguration,
        ?string $name = null)
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $command = $this->getApplication()->find('app:extract:extension');
        foreach ($this->bridgeConfiguration->getAllProjects() as $project) {
            if ($project->getCrowdinIdentifier() === 'typo3-cms') {
                continue;
            }

            try {
                $arguments = [
                    'command' => 'app:extract:extension',
                    'project' => $project->getCrowdinIdentifier(),
                ];
                $input = new ArrayInput($arguments);
                $command->run($input, $output);
            } catch (\Exception $e) {
                $output->writeln(sprintf('<error>Failed to process project %s: %s</error>', $project->getCrowdinIdentifier(), $e->getMessage()));
            }
        }

        return 0;
    }
}
