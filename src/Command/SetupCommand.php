<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CrowdinBridge\Configuration\Project;
use TYPO3\CrowdinBridge\Service\Management\ProjectService;

class SetupCommand extends Command
{

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('crowdin:setup')
            ->setDescription('Create configuration file')
            ->setHelp('The configuration.json file contains the crowdin ID and languages and '
                . 'reduces the amount of needed API calls.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Create `configuration.json` file');

        $service = new ProjectService();
        $projects = $service->updateConfiguration();
        sort($projects);
        $io->success(sprintf('%s projects have been configured', count($projects)));
        $io->note(implode(', ', $projects));

        return 0;
    }
}
