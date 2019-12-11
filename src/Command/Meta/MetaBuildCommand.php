<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Command\Meta;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CrowdinBridge\Command\BaseCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MetaBuildCommand extends BaseCommand
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
        $this->setupConfigurationService('');

        $command = $this->getApplication()->find('crowdin:build');
        foreach ($this->configurationService->getAllProjects() as $project) {
            $arguments = [
                'command' => 'crowdin:build',
                'project' => $project->getIdentifier(),
                'async' => true,
            ];
            $input = new ArrayInput($arguments);
            $command->run($input, $output);
        }
    }
}
