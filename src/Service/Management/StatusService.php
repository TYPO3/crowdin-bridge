<?php

declare(strict_types=1);

namespace App\Service\Management;

use App\Configuration\Project;
use App\Configuration\ProjectCollection;
use App\Crowdin\Repository\TranslationStatusRepository;
use App\Status\Overview\JsonStatusWriter;
use App\Status\Overview\PageStatusWriter;

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
        $projects = [];
        foreach ($this->projectCollection as $project) {
            $projects[$project->identifier] = [
                'crowdinProject' => $project,
                'translationProgress' => $this->translationStatusRepository->findByProjectId($project->id),
            ];
        }

        $output = [];

        $languagesOfCore = [];
        $output['languages'] = [];
        foreach ($projects['typo3-cms']['crowdinProject']->languages as $language) {
            $languagesOfCore[] = $language->id;
            $output['languages'][$language->id] = $language->name;
        }

        asort($output['languages']);
        sort($languagesOfCore);

        foreach ($projects as $project) {
            /** @var Project $crowdinProject */
            $crowdinProject = $project['crowdinProject'];

            $projectLine = [
                'extensionKey' => $crowdinProject->extensionKey,
                'crowdinKey' => $crowdinProject->identifier,
            ];

            $languageInfo = [];
            $projectUsable = false;

            foreach ($languagesOfCore as $languageOfCore) {
                $status = '-';
                foreach ($project['translationProgress'] as $translationProgress) {
                    if ($translationProgress->languageId === $languageOfCore) {
                        $status = $translationProgress->approvalProgress;
                        if ($status > 0) {
                            $projectUsable = true;
                        }
                    }
                }
                $languageInfo[$languageOfCore] = $status;
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
