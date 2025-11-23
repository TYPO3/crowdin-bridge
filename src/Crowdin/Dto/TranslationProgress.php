<?php

declare(strict_types=1);

namespace App\Crowdin\Dto;

use CrowdinApiClient\Model\Progress;

final readonly class TranslationProgress
{
    public function __construct(
        public string $languageId,
        public int $translationProgress,
        public int $approvalProgress,
        public int $phrasesTotal,
        public int $phrasesTranslated,
        public int $phrasesApproved,
    ) {}

    public static function fromCrowdinProgress(Progress $progress): self
    {
        $phrases = $progress->getPhrases();

        return new self(
            $progress->getLanguageId(),
            $progress->getTranslationProgress(),
            $progress->getApprovalProgress(),
            $phrases['total'],
            $phrases['translated'],
            $phrases['approved'],
        );
    }
}
