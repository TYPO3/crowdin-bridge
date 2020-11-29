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
use TYPO3\CrowdinBridge\Info\LanguageInformation;
use TYPO3\CrowdinBridge\Service\DownloadCrowdinTranslationService;

class CrowdinExtractExtCommand extends Command
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
        $projectIdentifier = $input->getArgument('project');
        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('Project %s', $projectIdentifier));

        try {
            $service = new DownloadCrowdinTranslationService();
            $service->downloadPackage($projectIdentifier);

            $message = 'Data has been downloaded!';
            $io->success($message);
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }

        return 0;
    }
}
