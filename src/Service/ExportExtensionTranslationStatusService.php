<?php

declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Service;

use CrowdinApiClient\Model\Language;
use CrowdinApiClient\Model\Progress;
use TYPO3\CrowdinBridge\Api\Wrapper\ProjectApi;
use TYPO3\CrowdinBridge\Info\LanguageInformation;
use TYPO3\CrowdinBridge\Utility\FileHandling;

class ExportExtensionTranslationStatusService
{

    /** @var ProjectApi */
    protected ProjectApi $projectApi;

    /** @var Language[] */
    protected array $allLanguages;

    public function __construct()
    {
        $this->projectApi = new ProjectApi();
        $this->allLanguages = LanguageInformation::getDetailedLanguageInformation();
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
            $languageId = $language->getLanguageId();
            $phrases = $language->getPhrases();
            if (isset($this->allLanguages[$languageId])) {
                $name = $this->allLanguages[$languageId]->getName();
            } else {
                $name = $languageId;
            }
            $simple[$languageId] = [
                'name' => $name,
                'code' => $languageId,
                'code_typo3' => LanguageInformation::getLanguageForTypo3($languageId),
                'phrases' => $phrases['total'] ?? '', // fallback
                'phrasesTranslated' => $phrases['translated'] ?? '',
                'phrasesApproved' => $phrases['approved'] ?? '',
                'progress' => $language->getApprovalProgress(),
            ];
        }
        return json_encode($simple, JSON_PRETTY_PRINT);
    }
}
