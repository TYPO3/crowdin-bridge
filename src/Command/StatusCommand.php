<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CrowdinBridge\Api\Wrapper\LanguageApi;
use TYPO3\CrowdinBridge\Api\Wrapper\ProjectApi;
use TYPO3\CrowdinBridge\Exception\NoApiCredentialsException;

class StatusCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('status')
            ->addArgument('project', InputArgument::REQUIRED, 'Project identifier')
            ->setDescription('Get status');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectIdentifier = $input->getArgument('project');

        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('Project %s', $projectIdentifier));

        $languageApi = new LanguageApi();
        $allLanguages = $languageApi->get();

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
//                        ['Count strings', $projectDetails->get^],
//                        ['Count words', $projectInformation['details']['total_words_count']],
                    ]
                );
            }

            $status = $projectApi->getTranslationStatus($projectIdentifier);
            if ($status) {
                $headers = [
                    'Name',
                    'Progress (%)'
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
                        ($s->getTranslationProgress() === $s->getApprovalProgress() ? $s->getApprovalProgress() : (sprintf('%s / %s', $s->getTranslationProgress(), $s->getApprovalProgress())))
                    ];
                }
                $io->section('Languages');
                $io->table($headers, $items);
            }
        } catch (NoApiCredentialsException $exception) {
            $io->warning(sprintf('Skipped: %s', $exception->getMessage()));
        }

        return 0;
    }
}
