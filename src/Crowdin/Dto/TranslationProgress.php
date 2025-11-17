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
    ) {}

    public static function fromCrowdinProgress(Progress $progress): self
    {
        return new self(
            $progress->getLanguageId(),
            $progress->getTranslationProgress(),
            $progress->getApprovalProgress(),
        );
    }
}
