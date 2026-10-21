<?php

declare(strict_types=1);

namespace App\Status\Core;

use App\Crowdin\Dto\BranchTranslationProgress;
use App\Crowdin\Dto\Language;
use App\Crowdin\Repository\LanguageRepository;
use App\Status\StatusProcessingException;
use Psr\Clock\ClockInterface;

readonly class StatusWriter
{
    public function __construct(
        private ClockInterface $clock,
        private LanguageRepository $languageRepository,
    ) {}

    /**
     * @param list<BranchTranslationProgress> $translationProgressesByBranch
     */
    public function write(string $filePath, array $translationProgressesByBranch): void
    {
        $languages = [];
        foreach (array_keys(array_first($translationProgressesByBranch)?->toArray()['translations'] ?? []) as $languageId) {
            $language = $this->languageRepository->findById($languageId);
            if ($language instanceof Language) {
                $languages[$language->id] = $language->name;
            }
        }

        $result = [
            'date' => $this->clock->now()->format(\DateTimeInterface::ATOM),
            'languages' => $languages,
            'branches' => array_map(
                static fn(BranchTranslationProgress $translationProgressByBranch): array => $translationProgressByBranch->toArray(),
                $translationProgressesByBranch,
            ),
        ];

        if (@file_put_contents($filePath, json_encode($result, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)) === false) {
            throw StatusProcessingException::fromJsonContentNotWritable($filePath);
        }
    }
}
