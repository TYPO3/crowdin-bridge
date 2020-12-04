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

class MetaExtractExtensionsCommand extends Command
{

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('meta:extractExtensions')
            ->setDescription('Meta :: Extract all extensions')
            ->setHelp('Download & process translations of all extensions');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bridgeConfiguration = new BridgeConfiguration();
        $allProjects = $bridgeConfiguration->getAllProjects();

        $command = $this->getApplication()->find('extract:ext');
        foreach ($allProjects as $project) {
            if ($project->getCrowdinIdentifier() === 'typo3-cms') {
                continue;
            }
            $arguments = [
                'command' => 'extract:extension',
                'project' => $project->getCrowdinIdentifier()
            ];
            $input = new ArrayInput($arguments);
            $command->run($input, $output);
        }

        return 0;
    }
}
