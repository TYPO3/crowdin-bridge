<?php

declare(strict_types=1);

namespace App\Service\Management;

use App\Configuration\Language;
use App\Configuration\ProjectCollection;
use App\Crowdin\Repository\TranslationStatusRepository;
use App\Status\Overview\JsonStatusWriter;
use App\Status\Overview\PageStatusWriter;
use App\Status\Overview\ProjectTranslationProgress;
use App\Status\Overview\ProjectTranslationProgressCollection;

final readonly class StatusService
{
    public function __construct(
        private JsonStatusWriter $jsonStatusWriter,
        private PageStatusWriter $pageStatusWriter,
        private ProjectCollection $projectCollection,
        private TranslationStatusRepository $translationStatusRepository,
    ) {}

    public function getStatus(): array
    {
        $projectTranslationProgressCollection = new ProjectTranslationProgressCollection();
        foreach ($this->projectCollection as $project) {
            $projectTranslationProgressCollection->add(
                new ProjectTranslationProgress(
                    $project,
                    $this->translationStatusRepository->findByProjectId($project->id)
                )
            );
        }

        $output = ['languages' => []];

        $coreLanguageIds = [];
        $coreLanguages = $projectTranslationProgressCollection->getCoreLanguages();
        usort($coreLanguages, static fn(Language $a, Language $b) => $a->id <=> $b->id);
        foreach ($coreLanguages as $language) {
            $coreLanguageIds[] = $language->id;
            $output['languages'][$language->id] = $language->name;
        }
        asort($output['languages']);

        foreach ($projectTranslationProgressCollection as $projectTranslationProgress) {
            $projectLine = [
                'extensionKey' => $projectTranslationProgress->project->extensionKey,
                'crowdinKey' => $projectTranslationProgress->project->identifier,
            ];

            $languageInfo = [];
            $projectUsable = false;

            foreach ($coreLanguageIds as $coreLanguageId) {
                $status = '-';
                foreach ($projectTranslationProgress->progresses as $progress) {
                    if ($progress->languageId === $coreLanguageId) {
                        $status = $progress->approvalProgress;
                        if ($status > 0) {
                            $projectUsable = true;
                        }
                    }
                }
                $languageInfo[$coreLanguageId] = $status;
            }
            $projectLine['languages'] = $languageInfo;
            $projectLine['usable'] = $projectUsable;

            $output['projects'][] = $projectLine;
        }

        $this->jsonStatusWriter->write($output);
        $this->pageStatusWriter->write();

        return $output;
    }
}
