<?php

declare(strict_types=1);

namespace App\Service;

use App\Configuration\Project;
use App\Crowdin\Dto\Language;
use App\Crowdin\Dto\TranslationProgress;
use App\Crowdin\Repository\LanguageRepository;
use App\Crowdin\Repository\TranslationStatusRepository;
use App\File\PathResolver;
use App\Info\LanguageInformation;
use App\Utility\FileHandling;

final readonly class ExportExtensionTranslationStatusService
{
    public function __construct(
        private LanguageRepository $languageRepository,
        private PathResolver $pathResolver,
        private TranslationStatusRepository $translationStatusRepository,
    ) {}

    public function export(Project $project): void
    {
        $translationProgresses = $this->translationStatusRepository->findByProjectId($project->id);
        if ($translationProgresses !== []) {
            $extensionKey = $project->extensionKey;

            $projectSubDir = $this->pathResolver->getRsyncPath() . sprintf('/%s/%s/%s-l10n/', $extensionKey[0], $extensionKey[1], $extensionKey);
            FileHandling::mkdir_deep($projectSubDir);

            $filename = $projectSubDir . $extensionKey . '.json';
            file_put_contents($filename, $this->simplifyProgresses($translationProgresses));
        }
    }

    /**
     * @param list<TranslationProgress> $translationProgresses
     */
    private function simplifyProgresses(array $translationProgresses): string
    {
        $simple = [];

        foreach ($translationProgresses as $progress) {
            $language = $this->languageRepository->findById($progress->languageId);
            $name = $language instanceof Language
                ? $language->name
                : $progress->languageId;
            $simple[$progress->languageId] = [
                'name' => $name,
                'code' => $progress->languageId,
                'code_typo3' => LanguageInformation::getLanguageForTypo3($progress->languageId),
                'phrases' => $progress->phrasesTotal,
                'phrasesTranslated' => $progress->phrasesTranslated,
                'phrasesApproved' => $progress->phrasesApproved,
                'progress' => $progress->approvalProgress,
            ];
        }
        return json_encode($simple, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }
}
