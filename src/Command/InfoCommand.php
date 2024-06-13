<?php

declare(strict_types=1);

namespace App\Command;

use App\Api\Wrapper\ProjectApi;
use App\Exception\NoApiCredentialsException;
use App\Info\LanguageInformation;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:info',
    description: 'Get info about a project',
    hidden: false
)]
class InfoCommand extends Command
{
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

        $allLanguages = LanguageInformation::getDetailedLanguageInformation();

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
                    if (isset($allLanguages[$s->getLanguageId()])) {
                        $languageInfo = $allLanguages[$s->getLanguageId()];
                        $languageName = sprintf('%s - %s', $languageInfo->getName(), $languageInfo->getId());
                    } else {
                        $languageName = $s->getLanguageId();
                    }
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
