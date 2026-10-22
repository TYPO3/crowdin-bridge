<?php

declare(strict_types=1);

namespace App\Crowdin\Dto;

use App\Crowdin\Entity\Branch;
use App\Crowdin\Entity\Directory;

final readonly class DirectoryTranslationProgress
{
    /**
     * @param list<TranslationProgress> $translationProgress
     */
    public function __construct(
        public Branch $branch,
        public Directory $directory,
        public array $translationProgress,
    ) {}

    /**
     * @return array{
     *     translations: array<string, int>,
     *     approvals: array<string, int>
     * }
     */
    public function translationProgressToArray(): array
    {
        $translations = [];
        $approvals = [];
        foreach ($this->translationProgress as $progress) {
            $translations[$progress->languageId] = $progress->translationProgress;
            $approvals[$progress->languageId] = $progress->approvalProgress;
        }

        return [
            'translations' => $translations,
            'approvals' => $approvals,
        ];
    }
}
