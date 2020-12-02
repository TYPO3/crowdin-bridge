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
use TYPO3\CrowdinBridge\Entity\BridgeConfiguration;
use TYPO3\CrowdinBridge\Service\DownloadCrowdinTranslationService;
use TYPO3\CrowdinBridge\Utility\FileHandling;

class CrowdinExtractCoreCommand extends Command
{

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('crowdin:extract:core')
            ->setDescription('Download translations of TYPO3 core')
            ->addArgument('language', InputArgument::OPTIONAL, 'List of languages or use "*" for all', '*');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bridgeConfiguration = new BridgeConfiguration();
        $project = $bridgeConfiguration->getProject('typo3-cms');

        $io = new SymfonyStyle($input, $output);
        $io->title('Project typo3-cms');

        $languages = $input->getArgument('language') ?? '*';
        $languageList = $languages === '*' ? $project->getLanguages() : FileHandling::trimExplode(',', $languages, true);

        $service = new DownloadCrowdinTranslationService();
        $service->downloadPackageCore('typo3-cms', $languageList);

        $io->success(sprintf('Core process finished for the following languages: %s', implode(', ', $languageList)));
        return 0;
    }
}
