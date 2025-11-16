<?php

declare(strict_types=1);

namespace App\Service\Management;

use App\Api\Wrapper\ProjectApi;
use App\Configuration\ProjectCollection;
use App\Entity\ProjectConfiguration;
use App\Exception\ExtensionNotAvailableInFileConfigurationException;
use App\Status\Overview\JsonStatusWriter;
use App\Status\Overview\PageStatusWriter;
use CrowdinApiClient\Model\Progress;
use FriendsOfTYPO3\CrowdinBase\Configuration\Entity\Project;

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
            $tmp = [
                'crowdinProject' => $project,
                'localProject' => null,
                'translationStatus' => $this->projectApi->getTranslationStatusByCrowdinId($project->id),
            ];
            try {
                $tmp['localProject'] = $this->projectApi->getConfiguration()->getProjectByCrowdinId($project->id);
            } catch (ExtensionNotAvailableInFileConfigurationException) {
                // do nothing
            }
            $collection[$project->identifier] = $tmp;
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
            /** @var ProjectConfiguration $localProject */
            $localProject = $item['localProject'];
            /** @var Project $crowdinProject */
            $crowdinProject = $item['crowdinProject'];

            $projectLine = [
                'extensionKey' => $localProject ? $localProject->getExtensionkey() : '',
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
