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

class MetaExtractExtCommand extends Command
{

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('crowdin:meta:extractExt')
            ->setDescription('Meta :: Extract all extensions')
            ->setHelp('Download all translations of extensions');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setupConfigurationService('');

        $command = $this->getApplication()->find('crowdin:extract:ext');
        foreach ($this->configurationService->getAllProjects() as $project) {
            if ($project->getIdentifier() === 'typo3-cms') {
                continue;
            }
            $arguments = [
                'command' => 'crowdin:extract:ext',
                'project' => $project->getIdentifier()
            ];
            $input = new ArrayInput($arguments);
            $command->run($input, $output);
        }
    }
}
