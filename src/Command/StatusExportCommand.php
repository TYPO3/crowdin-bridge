<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Command;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CrowdinBridge\Exception\ConfigurationException;
use TYPO3\CrowdinBridge\Exception\NoApiCredentialsException;
use TYPO3\CrowdinBridge\Service\ExportStatusService;
use TYPO3\CrowdinBridge\Service\StatusService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class StatusExportCommand extends BaseCommand
{

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('crowdin:status.export')
            ->addArgument('project', InputArgument::REQUIRED, 'Project identifier')
            ->setDescription('Export status');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setupConfigurationService($input->getArgument('project'));
        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('Project %s', $this->getProject()->getIdentifier()));


        try {
            $exportService = new ExportStatusService($this->configurationService->getProject()->getIdentifier());
            $exportService->export();
        } catch (NoApiCredentialsException $e) {
            $io->error($e->getMessage());
        } catch (ConfigurationException $e) {
            $io->error($e->getMessage());
        }

    }
}
