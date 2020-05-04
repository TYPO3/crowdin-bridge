<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Command;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CrowdinBridge\Exception\NoApiCredentialsException;
use TYPO3\CrowdinBridge\Service\InfoService;
use TYPO3\CrowdinBridge\Service\StatusService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class StatusCommand extends BaseCommand
{

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('crowdin:status')
            ->addArgument('project', InputArgument::REQUIRED, 'Project identifier')
            ->setDescription('Get status');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setupConfigurationService($input->getArgument('project'));
        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('Project %s', $this->getProject()->getIdentifier()));

        try {
            $statusService = new StatusService($this->getProject()->getIdentifier());
            $infoService = new InfoService($this->getProject()->getIdentifier());

            $projectInformation = $infoService->get();

            $io->section('General Information');
            $io->table(
                ['Name', 'Value'],
                [
                    ['Name', $projectInformation['details']['name']],
                    ['Last Build', $projectInformation['details']['last_build']],
                    ['Last Activity', $projectInformation['details']['last_activity']],
                    ['Count strings', $projectInformation['details']['total_strings_count']],
                    ['Count words', $projectInformation['details']['total_words_count']],
                    ['Invite URL translator', $projectInformation['details']['invite_url']['translator']],
                    ['Invite URL proof reader', $projectInformation['details']['invite_url']['proofreader']],
                ]
            );

            $status = $statusService->get();
            if ($status) {
                $languageCodes = [];
                $headers = [
                    'Name',
                    'Progress (%)'
                ];
                $items = [];
                foreach ($status as $s) {
                    $languageCodes[] = $s['code'];
                    $items[] = [
                        sprintf('%s - %s', $s['name'], $s['code']),
                        ($s['translated_progress'] === $s['approved_progress'] ? $s['approved_progress'] : (sprintf('%s / %s', $s['translated_progress'], $s['approved_progress'])))

                    ];
                }
                $io->section('Languages');
                $io->table($headers, $items);

                if (!empty(array_diff($languageCodes, $this->configurationService->getProject()->getLanguages()))) {
                    sort($languageCodes);
                    $this->configurationService->updateSingleConfiguration($this->getProject()->getIdentifier(), 'languages', implode(',', $languageCodes));
                }
            }
        } catch (NoApiCredentialsException $exception) {
            $io->warning(sprintf('Skipped: %s', $exception->getMessage()));
        }
    }
}
