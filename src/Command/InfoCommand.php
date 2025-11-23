<?php

declare(strict_types=1);

namespace App\Command;

use App\Api\Wrapper\ProjectApi;
use App\Configuration\Project;
use App\Configuration\ProjectCollection;
use App\Crowdin\Dto\Language;
use App\Crowdin\Repository\LanguageRepository;
use App\Exception\NoApiCredentialsException;
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
    ) {
        parent::__construct();
    }

    protected function configure()
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
            $projectApi = new ProjectApi();
            $projectDetails = $projectApi->getProject($projectIdentifier);
            if ($projectDetails) {
                $io->section('General Information');
                $io->table(
                    ['Name', 'Value'],
                    [
                        ['Name', $projectDetails->getName()],
                        ['Last Activity', $projectDetails->getLastActivity()],
                    ]
                );
            }

            $status = $projectApi->getTranslationStatus($projectIdentifier);
            if ($status) {
                $headers = [
                    'Name',
                    'Progress (%)',
                ];
                $items = [];
                foreach ($status as $s) {
                    $language = $this->languageRepository->findById($s->getLanguageId());
                    $languageName = $language instanceof Language
                        ? sprintf('%s - %s', $language->name, $language->id)
                        : $s->getLanguageId();
                    $items[] = [
                        $languageName,
                        ($s->getTranslationProgress() === $s->getApprovalProgress() ? $s->getApprovalProgress() : (sprintf('%s / %s', $s->getTranslationProgress(), $s->getApprovalProgress()))),
                    ];
                }
                $io->section('Languages');
                $io->table($headers, $items);
            }
        } catch (NoApiCredentialsException $exception) {
            $io->warning(sprintf('Skipped: %s', $exception->getMessage()));
        }

        return Command::SUCCESS;
    }
}
