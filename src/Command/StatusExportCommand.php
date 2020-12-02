<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CrowdinBridge\Service\ExportExtensionTranslationStatusService;

class StatusExportCommand extends Command
{

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('crowdin:status.export')
            ->addArgument('extensionKey', InputArgument::REQUIRED, 'Extension Key')
            ->setDescription('Export extension translation status');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $extensionKey = $input->getArgument('extensionKey');
        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('Extension %s', $extensionKey));

        $exportService = new ExportExtensionTranslationStatusService();
        $exportService->export($extensionKey);

        return 0;
    }
}
