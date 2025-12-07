<?php

declare(strict_types=1);

namespace App\Command;

use App\Configuration\Project;
use App\Configuration\ProjectCollection;
use App\Crowdin\Dto\Language;
use App\Crowdin\Repository\LanguageRepository;
use App\Crowdin\Repository\ProjectRepository;
use App\Crowdin\Repository\TranslationStatusRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Output some information about the project status on crowdin
 */
#[AsCommand(
    name: 'app:info',
    description: 'Get info about a project',
)]
final class InfoCommand extends Command
{
    public function __construct(
        private readonly LanguageRepository $languageRepository,
        private readonly ProjectCollection $projectCollection,
        private readonly ProjectRepository $projectRepository,
        private readonly TranslationStatusRepository $translationStatusRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('project', InputArgument::REQUIRED, 'Project identifier');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectIdentifier = $input->getArgument('project');

        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('Project %s', $projectIdentifier));

        $project = $this->projectCollection->findByIdentifier($projectIdentifier);
        if (!$project instanceof Project) {
            $io->error(\sprintf('Project "%s" does not exist', $projectIdentifier));
            return Command::FAILURE;
        }

        try {
            $projectDetails = $this->projectRepository->findById($project->id);
            if ($projectDetails) {
                $io->section('General Information');
                $io->table(
                    ['Name', 'Value'],
                    [
                        ['Name', $projectDetails->name],
                        ['Created at', $projectDetails->createdAt->format('r')],
                        ['Updated at', $projectDetails->updatedAt->format('r')],
                        ['Last activity', $projectDetails->lastActivity->format('r')],
                    ]
                );
            }

            $progresses = $this->translationStatusRepository->findByProjectId($project->id);
            $headers = [
                'Name',
                'Progress (%)',
            ];
            $items = [];
            foreach ($progresses as $progress) {
                $language = $this->languageRepository->findById($progress->languageId);
                $languageName = $language instanceof Language
                    ? sprintf('%s - %s', $language->name, $language->id)
                    : $progress->languageId;
                $items[] = [
                    $languageName,
                    $progress->translationProgress === $progress->approvalProgress
                        ? $progress->approvalProgress
                        : sprintf('%s / %s', $progress->translationProgress, $progress->approvalProgress),
                ];
            }
            $io->section('Languages');
            $io->table($headers, $items);
        } catch (\Throwable $t) {
            $io->error(sprintf('An error occurred: %s', $t->getMessage()));
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
