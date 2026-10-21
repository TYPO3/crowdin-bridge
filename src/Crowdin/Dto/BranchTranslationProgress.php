<?php

declare(strict_types=1);

namespace App\Crowdin\Dto;

use App\Crowdin\Entity\Branch;

final readonly class BranchTranslationProgress
{
    /**
     * @param list<TranslationProgress> $translationProgress
     */
    public function __construct(
        public Branch $branch,
        public array $translationProgress,
    ) {}

    /**
     * @return array{
     *     branch: string,
     *     translations: array<string, int>,
     *     approvals: array<string, int>
     * }
     */
    public function toArray(): array
    {
        $translations = [];
        $approvals = [];
        foreach ($this->translationProgress as $progress) {
            $translations[$progress->languageId] = $progress->translationProgress;
            $approvals[$progress->languageId] = $progress->approvalProgress;
        }

        return [
            'branch' => $this->branch->name,
            'translations' => $translations,
            'approvals' => $approvals,
        ];
    }
}
