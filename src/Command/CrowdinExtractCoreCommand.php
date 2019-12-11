<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Command;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CrowdinBridge\Info\LanguageInformation;
use TYPO3\CrowdinBridge\Service\DownloadCrowdinTranslationService;
use TYPO3\CrowdinBridge\Utility\FileHandling;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CrowdinExtractCoreCommand extends BaseCommand
{

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('crowdin:extract:core')
            ->setDescription('Download CORE translations')
            ->addArgument('language', InputArgument::REQUIRED, 'Language');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setupConfigurationService('typo3-cms');

        $io = new SymfonyStyle($input, $output);
        $project = $this->getProject();
        $io->title(sprintf('Project %s', $project->getIdentifier()));

        $languages = $input->getArgument('language') ?? '*';
        $languageList = $languages === '*' ? $project->getLanguages() : FileHandling::trimExplode(',', $languages, true);

        $service = new DownloadCrowdinTranslationService($this->getProject()->getIdentifier());
        foreach ($languageList as $language) {
            try {
                $service->downloadPackage($language);

                $message = sprintf('Data has been downloaded for %s!', $language);

                $typo3LanguageIdentifier = LanguageInformation::getLanguageForTypo3($language);
                if ($typo3LanguageIdentifier !== $language) {
                    $message .= sprintf("\nTYPO3 language identifier is %s.", $typo3LanguageIdentifier);
                }
                $io->success($message);
            } catch (\Exception $e) {
                $io->error($e->getMessage());
            }
        }
    }
}
