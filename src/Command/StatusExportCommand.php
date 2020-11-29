<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Command;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CrowdinBridge\Exception\ConfigurationException;
use TYPO3\CrowdinBridge\Exception\NoApiCredentialsException;
use TYPO3\CrowdinBridge\Service\ExportStatusService;

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
            ->setDescription('Export status');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $extensionKey = $input->getArgument('extensionKey');
        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('Extension %s', $extensionKey));

        try {
            $exportService = new ExportStatusService();
            $exportService->export($extensionKey);
        } catch (NoApiCredentialsException $e) {
            $io->error($e->getMessage());
        } catch (ConfigurationException $e) {
            $io->error($e->getMessage());
        }

        return 0;
    }
}
