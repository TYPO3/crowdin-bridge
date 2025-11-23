<?php

declare(strict_types=1);

namespace App\Status\Project;

use App\Crowdin\Dto\Language;
use App\Crowdin\Dto\TranslationProgress;
use App\Crowdin\Repository\LanguageRepository;
use App\Info\LanguageInformation;
use App\Status\StatusProcessingException;

final readonly class StatusWriter
{
    public function __construct(
        private LanguageRepository $languageRepository,
    ) {}

    /**
     * @param list<TranslationProgress> $translationProgresses
     */
    public function write(string $filePath, array $translationProgresses): void
    {
        $result = [];
        foreach ($translationProgresses as $progress) {
            $language = $this->languageRepository->findById($progress->languageId);
            $name = $language instanceof Language
                ? $language->name
                : $progress->languageId;
            $result[$progress->languageId] = [
                'name' => $name,
                'code' => $progress->languageId,
                'code_typo3' => LanguageInformation::getLanguageForTypo3($progress->languageId),
                'phrases' => $progress->phrasesTotal,
                'phrasesTranslated' => $progress->phrasesTranslated,
                'phrasesApproved' => $progress->phrasesApproved,
                'progress' => $progress->approvalProgress,
            ];
        }

        if (@file_put_contents($filePath, json_encode($result, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)) === false) {
            throw StatusProcessingException::fromJsonContentNotWritable($filePath);
        }
    }
}
