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

class CrowdinExtractExtCommand extends BaseCommand
{

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('crowdin:extract:ext')
            ->addArgument('project', InputArgument::REQUIRED, 'Project identifier')
            ->setDescription('Download Extension translations');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setupConfigurationService($input->getArgument('project'));

        $io = new SymfonyStyle($input, $output);
        $project = $this->getProject();
        $io->title(sprintf('Project %s', $project->getIdentifier()));

        $languageList = $project->getLanguages();
        $branch = $project->getBranch();

        foreach ($languageList as $language) {
            try {
                $service = new DownloadCrowdinTranslationService($project->getIdentifier());
                $service->downloadPackage($language, $branch);

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
