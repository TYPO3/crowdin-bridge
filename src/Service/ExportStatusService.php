<?php

declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Service;

use Akeneo\Crowdin\Api\Download;
use TYPO3\CrowdinBridge\Exception\NoTranslationsAvailableException;
use TYPO3\CrowdinBridge\Info\CoreInformation;
use TYPO3\CrowdinBridge\Info\LanguageInformation;
use TYPO3\CrowdinBridge\Utility\FileHandling;
use ZipArchive;

class ExportStatusService extends BaseService
{

    public function export(): void
    {
        $project = $this->configurationService->getProject();
        $statusService = new StatusService($project->getIdentifier());

        $status = $statusService->get();
        if ($status) {
            $extensionName = $project->getExtensionkey();

            $projectSubDir = $this->configurationService->getPathRsync() . sprintf('%s/%s/%s-l10n/', $extensionName{0}, $extensionName{1}, $extensionName);
            FileHandling::mkdir_deep($projectSubDir);

            $filename = $projectSubDir . $extensionName . '.json';
            file_put_contents($filename, $this->simplifyStatus($status));
        }
    }

    protected function simplifyStatus(array $languages): string
    {
        $simple = [];

        foreach ($languages as $language) {
            $simple[$language['code']] = [
                'name' => $language['name'],
                'code' => $language['code'],
                'code_typo3' => LanguageInformation::getLanguageForTypo3($language['code']),
                'phrases' => $language['phrases'],
                'progress' => $language['approved_progress'],
            ];
        }
        return json_encode($simple, JSON_PRETTY_PRINT);
    }
}
