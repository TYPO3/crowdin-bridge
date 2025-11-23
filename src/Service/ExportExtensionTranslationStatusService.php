<?php

declare(strict_types=1);

namespace App\Service;

use App\Api\Wrapper\ProjectApi;
use App\Crowdin\Dto\Language;
use App\Crowdin\Repository\LanguageRepository;
use App\File\PathResolver;
use App\Info\LanguageInformation;
use App\Utility\FileHandling;
use CrowdinApiClient\Model\Progress;

final readonly class ExportExtensionTranslationStatusService
{
    public function __construct(
        private LanguageRepository $languageRepository,
        private PathResolver $pathResolver,
        private ProjectApi $projectApi
    ) {}

    public function export(string $extensionKey): void
    {
        $localProject = $this->projectApi->getConfiguration()->getProjectByExtensionKey($extensionKey);
        $translationStatus = $this->projectApi->getTranslationStatusByCrowdinId($localProject->getId());
        if ($translationStatus) {
            $extensionName = $localProject->getExtensionKey();

            $projectSubDir = $this->pathResolver->getRsyncPath() . sprintf('/%s/%s/%s-l10n/', $extensionName[0], $extensionName[1], $extensionName);
            FileHandling::mkdir_deep($projectSubDir);

            $filename = $projectSubDir . $extensionName . '.json';
            file_put_contents($filename, $this->simplifyStatus($translationStatus));
        }
    }

    /**
     * @param Progress[] $translationStatus
     */
    private function simplifyStatus(array $translationStatus): string
    {
        $simple = [];

        foreach ($translationStatus as $language) {
            $languageId = $language->getLanguageId();
            $phrases = $language->getPhrases();
            $languageInformation = $this->languageRepository->findById($languageId);
            $name = $languageInformation instanceof Language
                ? $languageInformation->name
                : $languageId;
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
