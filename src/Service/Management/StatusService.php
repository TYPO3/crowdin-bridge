<?php

declare(strict_types=1);

namespace App\Service\Management;

use App\Api\Wrapper\ProjectApi;
use App\Configuration\Project;
use App\Configuration\ProjectCollection;
use App\Status\Overview\JsonStatusWriter;
use App\Status\Overview\PageStatusWriter;
use CrowdinApiClient\Model\Progress;

final readonly class StatusService
{
    public function __construct(
        private JsonStatusWriter $jsonStatusWriter,
        private PageStatusWriter $pageStatusWriter,
        private ProjectCollection $projectCollection,
        private ProjectApi $projectApi
    ) {}

    public function getStatus(): array
    {
        $collection = [];
        foreach ($this->projectCollection as $project) {
            $collection[$project->identifier] = [
                'crowdinProject' => $project,
                'translationStatus' => $this->projectApi->getTranslationStatusByCrowdinId($project->id),
            ];
        }

        $output = [];

        $languagesOfCore = [];
        $output['languages'] = [];
        foreach ($collection['typo3-cms']['crowdinProject']->languages as $language) {
            $languagesOfCore[] = $language->id;
            $output['languages'][$language->id] = $language->name;
        }

        asort($output['languages']);
        sort($languagesOfCore);

        foreach ($collection as $item) {
            /** @var Project $crowdinProject */
            $crowdinProject = $item['crowdinProject'];

            $projectLine = [
                'extensionKey' => $crowdinProject->extensionKey,
                'crowdinKey' => $crowdinProject->identifier,
            ];

            $languageInfo = [];
            $projectUsable = false;

            foreach ($languagesOfCore as $languageOfCore) {
                $status = '-';
                foreach ($item['translationStatus'] as $language) {
                    /** @var Progress $language */
                    if ($language->getLanguageId() === $languageOfCore) {
                        $status = $language->getApprovalProgress();
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
