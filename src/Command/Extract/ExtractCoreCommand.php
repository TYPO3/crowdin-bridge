<?php

declare(strict_types=1);

namespace App\Command\Extract;

use App\Configuration\Language;
use App\Configuration\Project;
use App\Configuration\ProjectCollection;
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
    description: 'Download translations of TYPO3 Core',
)]
final class ExtractCoreCommand extends Command
{
    public function __construct(
        private readonly ProjectCollection $projectCollection,
        private readonly DownloadCrowdinTranslationService $downloadCrowdinTranslationService,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument('language', InputArgument::OPTIONAL, 'Comma-separated list of languages (for example: de,fr,it) or use "*" for all', '*');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('TYPO3 Core (typo3-cms)');

        $project = $this->projectCollection->findByIdentifier('typo3-cms');
        if (!$project instanceof Project) {
            $io->error('Core project cannot be found!');
            return Command::FAILURE;
        }

        $languages = $input->getArgument('language') ?? '*';
        $languageList = $languages === '*'
            ? array_map(static fn(Language $language): string => $language->id, $project->languages)
            : FileHandling::trimExplode(',', $languages, true);

        $this->downloadCrowdinTranslationService->downloadPackageCore($languageList);

        $io->success(sprintf('Core process finished for the following languages: %s', implode(', ', $languageList)));
        return Command::SUCCESS;
    }
}
