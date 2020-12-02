<?php

declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Service;

use CrowdinApiClient\Model\Progress;
use TYPO3\CrowdinBridge\Api\Wrapper\ProjectApi;
use TYPO3\CrowdinBridge\Info\LanguageInformation;
use TYPO3\CrowdinBridge\Utility\FileHandling;

class ExportExtensionTranslationStatusService
{

    /** @var ProjectApi */
    protected ProjectApi $projectApi;

    public function __construct()
    {
        $this->projectApi = new ProjectApi();
    }

    public function export(string $extensionKey): void
    {
        $localProject = $this->projectApi->getConfiguration()->getProjectByExtensionKey($extensionKey);
        $translationStatus = $this->projectApi->getTranslationStatusByCrowdinId($localProject->getId());
        if ($translationStatus) {
            $extensionName = $localProject->getExtensionkey();

            $projectSubDir = $this->projectApi->getConfiguration()->getPathRsync() . sprintf('%s/%s/%s-l10n/', $extensionName[0], $extensionName[1], $extensionName);
            FileHandling::mkdir_deep($projectSubDir);

            $filename = $projectSubDir . $extensionName . '.json';
            file_put_contents($filename, $this->simplifyStatus($translationStatus));
        }
    }

    /**
     * @param Progress[] $translationStatus
     * @return string
     */
    protected function simplifyStatus(array $translationStatus): string
    {
        $simple = [];

        foreach ($translationStatus as $language) {
            $simple[$language->getLanguageId()] = [
                'name' => $language->getLanguageId(),
                'code' => $language->getLanguageId(),
                'code_typo3' => LanguageInformation::getLanguageForTypo3($language->getLanguageId()),
                'phrases' => $language->getPhrases(),
                'progress' => $language->getApprovalProgress(),
            ];
        }
        return json_encode($simple, JSON_PRETTY_PRINT);
    }
}
