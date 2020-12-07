<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Command\Management;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CrowdinBridge\Configuration\Project;
use TYPO3\CrowdinBridge\Service\Management\StatusService;

class StatusCommand extends Command
{

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('management:status')
            ->setDescription('Status of all Crowdin Projects');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Status of all projects');

        $service = new StatusService();
        $response = $service->getStatus(true);

        $io->info('Status has been exported!');
        return 0;
    }

    private function spread(array $existing, array $add): array
    {
        foreach ($add as $value) {
            $existing[] = $value;
        }
        return $existing;
    }

}
