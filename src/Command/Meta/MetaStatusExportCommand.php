<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Command\Meta;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CrowdinBridge\Command\BaseCommand;
use TYPO3\CrowdinBridge\Entity\BridgeConfiguration;

class MetaStatusExportCommand extends Command
{

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('meta:status.export')
            ->setDescription('Meta :: Export status of projects');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('status.export');
        $bridgeConfiguration = new BridgeConfiguration();
        foreach ($bridgeConfiguration->getAllProjects() as $project) {
            if ($project->isCoreProject()) {
                continue;
            }
            $arguments = [
                'command' => 'status.export',
                'extensionKey' => $project->getExtensionkey()
            ];
            $input = new ArrayInput($arguments);
            $command->run($input, $output);
        }

        return 0;
    }
}
