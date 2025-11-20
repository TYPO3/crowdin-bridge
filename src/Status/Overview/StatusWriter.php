<?php

declare(strict_types=1);

namespace App\Status\Overview;

use App\Configuration\Language;
use App\Configuration\ProjectCollection;
use App\Console\Output\OutputInterface;
use App\Crowdin\Repository\TranslationStatusRepository;

final readonly class StatusWriter
{
    public function __construct(
        private JsonStatusWriter $jsonStatusWriter,
        private PageStatusWriter $pageStatusWriter,
        private ProjectCollection $projectCollection,
        private TranslationStatusRepository $translationStatusRepository,
    ) {}

    public function write(OutputInterface $output): void
    {
        $errors = [];

        $output->start(count($this->projectCollection));

        $projectTranslationProgressCollection = new ProjectTranslationProgressCollection();
        foreach ($this->projectCollection as $project) {
            try {
                $projectTranslationProgressCollection->add(
                    new ProjectTranslationProgress(
                        $project,
                        $this->translationStatusRepository->findByProjectId($project->id)
                    )
                );
            } catch (\Throwable $t) {
                $errors[] = \sprintf(
                    'Error while retrieving progress for project "%s": %s',
                    $project->identifier,
                    $t->getMessage(),
                );
            }
            $output->advance(\sprintf(
                'Translation status for project "%s" retrieved',
                $project->identifier,
            ));
        }

        $result = ['languages' => []];

        $coreLanguageIds = [];
        $coreLanguages = $projectTranslationProgressCollection->getCoreLanguages();
        usort($coreLanguages, static fn(Language $a, Language $b) => $a->id <=> $b->id);
        foreach ($coreLanguages as $language) {
            $coreLanguageIds[] = $language->id;
            $result['languages'][$language->id] = $language->name;
        }
        asort($result['languages']);

        foreach ($projectTranslationProgressCollection as $projectTranslationProgress) {
            $projectLine = [
                'extensionKey' => $projectTranslationProgress->project->extensionKey,
                'crowdinKey' => $projectTranslationProgress->project->identifier,
            ];

            $translationProgress = [];
            $approvalProgress = [];
            $translationsAvailable = false;
            $projectUsable = false;

            foreach ($coreLanguageIds as $coreLanguageId) {
                $translationStatus = '-';
                $approvalStatus = '-';
                foreach ($projectTranslationProgress->progresses as $progress) {
                    if ($progress->languageId === $coreLanguageId) {
                        $translationStatus = $progress->translationProgress;
                        $approvalStatus = $progress->approvalProgress;
                        if ($translationStatus > 0) {
                            $translationsAvailable = true;
                        }
                        if ($approvalStatus > 0) {
                            $projectUsable = true;
                        }
                    }
                }
                $translationProgress[$coreLanguageId] = $translationStatus;
                $approvalProgress[$coreLanguageId] = $approvalStatus;
            }
            $projectLine['translations'] = $translationProgress;
            $projectLine['approvals'] = $approvalProgress;
            $projectLine['translationsAvailable'] = $translationsAvailable;
            $projectLine['usable'] = $projectUsable;

            $result['projects'][] = $projectLine;
        }

        try {
            $this->jsonStatusWriter->write($result);
            $this->pageStatusWriter->write();
        } catch (\Throwable $t) {
            $errors[] = 'An error occurred while writing the status: ' . $t->getMessage();
        }

        $output->finish($errors);
    }
}
