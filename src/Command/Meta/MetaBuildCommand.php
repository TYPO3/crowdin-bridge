<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Command\Meta;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CrowdinBridge\Command\BaseCommand;
use TYPO3\CrowdinBridge\Entity\BridgeConfiguration;

class MetaBuildCommand extends Command
{

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('crowdin:meta:build')
            ->setDescription('Meta :: Trigger build of a project')
            ->setHelp('Only if a project has been exported it is possible to get the latest translations. ');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bridgeConfiguration = new BridgeConfiguration();

        $command = $this->getApplication()->find('crowdin:build');
        foreach ($bridgeConfiguration->getAllProjects() as $project) {
            $arguments = [
                'command' => 'crowdin:build',
                'project' => $project->getCrowdinIdentifier(),
            ];
            $input = new ArrayInput($arguments);
            $command->run($input, $output);
        }

        return 0;
    }
}
