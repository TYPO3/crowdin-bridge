<?php

declare(strict_types=1);

namespace App\Command\Extract;

use App\Configuration\Project;
use App\Configuration\ProjectCollection;
use App\Service\DownloadCrowdinTranslationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:extract:extension',
    description: 'Download translations of TYPO3 extension',
)]
final class ExtractExtensionCommand extends Command
{
    private array $skippedExtensions = [
        'typo3-extension-pastereference' => 'https://github.com/Kephson/paste_reference/issues/38',
    ];

    public function __construct(
        private readonly DownloadCrowdinTranslationService $downloadCrowdinTranslationService,
        private readonly ProjectCollection $projectCollection,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('project', InputArgument::OPTIONAL, 'Project identifier')
            ->addArgument('languages', InputArgument::IS_ARRAY, 'Project identifier');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $projectIdentifier = $input->getArgument('project');
        $languages = (array)$input->getArgument('languages');

        if ($projectIdentifier) {
            $project = $this->projectCollection->findByIdentifier($projectIdentifier);
            if (!$project instanceof Project) {
                $io->error(sprintf('Project "%s" does not exist', $projectIdentifier));
                return Command::FAILURE;
            }
            if ($project->isCoreProject()) {
                $io->error('Extract "typo3-cms" with app:extract:core');
                return Command::FAILURE;
            }
            if (isset($this->skippedExtensions[$projectIdentifier])) {
                $io->warning(sprintf('Extension "%s" is skipped: %s', $projectIdentifier, $this->skippedExtensions[$projectIdentifier]));
                return Command::FAILURE;
            }
            $this->downloadProject($projectIdentifier, $languages, true, $io);
            return Command::SUCCESS;
        }

        $verbose = $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
        $progressBar = new ProgressBar($output, count($this->projectCollection));
        $progressBar->start();

        foreach ($this->projectCollection as $project) {
            $this->downloadProject($project->identifier, $languages, $verbose, $io);
            $progressBar->advance();
        }
        $progressBar->finish();
        return Command::SUCCESS;
    }

    protected function downloadProject(string $projectIdentifier, array $listOfLanguages, bool $verbose, SymfonyStyle $io): void
    {
        if ($projectIdentifier === 'typo3-cms') {
            return;
        }
        if ($verbose) {
            $io->title(sprintf('Extension "%s"', $projectIdentifier));
        }

        if (isset($this->skippedExtensions[$projectIdentifier])) {
            $io->warning(sprintf('Extension "%s" is skipped: %s', $projectIdentifier, $this->skippedExtensions[$projectIdentifier]));
            return;
        }

        try {
            $result = $this->downloadCrowdinTranslationService->downloadPackageExtension($projectIdentifier, $listOfLanguages);

            if ($verbose) {
                $io->success('Data has been downloaded!');

                $headers = [
                    'Language',
                    'Files',
                ];
                $items = [];
                foreach ($result as $language => $fileCount) {
                    $items[] = [$language, $fileCount];

                }
                $io->section('Export');
                $io->table($headers, $items);
            }
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }
    }

}
