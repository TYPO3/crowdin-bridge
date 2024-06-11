<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\BridgeConfiguration;
use App\Service\DownloadCrowdinTranslationService;
use App\Utility\FileHandling;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:extract:core',
    description: 'Download translations of TYPO3 core',
    hidden: false
)]
class ExtractCoreCommand extends Command
{
    public function __construct(
        protected readonly BridgeConfiguration $bridgeConfiguration,
        protected DownloadCrowdinTranslationService $downloadCrowdinTranslationService,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->addArgument('language', InputArgument::OPTIONAL, 'List of languages or use "*" for all', '*');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $project = $this->bridgeConfiguration->getProject('typo3-cms');

        $io = new SymfonyStyle($input, $output);
        $io->title('TYPO3 Core (typo3-cms)');

        $languages = $input->getArgument('language') ?? '*';
        $languageList = $languages === '*' ? $project->getLanguages() : FileHandling::trimExplode(',', $languages, true);

        $this->downloadCrowdinTranslationService->downloadPackageCore('typo3-cms', $languageList);

        $io->success(sprintf('Core process finished for the following languages: %s', implode(', ', $languageList)));
        return 0;
    }
}
