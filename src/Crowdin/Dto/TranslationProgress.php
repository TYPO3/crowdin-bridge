<?php

declare(strict_types=1);

namespace App\Crowdin\Dto;

use CrowdinApiClient\Model\Progress;

final readonly class TranslationProgress
{
    private function __construct(
        public string $languageId,
        public int $approvalProgress,
    ) {}

    public static function fromCrowdinProgress(Progress $progress): self
    {
        return new self(
            $progress->getLanguageId(),
            $progress->getApprovalProgress(),
        );
    }
}
