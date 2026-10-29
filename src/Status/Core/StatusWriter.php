<?php

declare(strict_types=1);

namespace App\Status\Core;

use App\Crowdin\Dto\BranchTranslationProgress;
use App\Crowdin\Dto\DirectoryTranslationProgress;
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
     * @param list<DirectoryTranslationProgress> $translationProgressesByDirectory
     */
    public function write(string $filePath, array $translationProgressesByBranch, array $translationProgressesByDirectory): void
    {
        $languages = [];
        foreach (array_keys(array_first($translationProgressesByBranch)?->toArray()['translations'] ?? []) as $languageId) {
            $language = $this->languageRepository->findById($languageId);
            if ($language instanceof Language) {
                $languages[$language->id] = $language->name;
            }
        }
        uasort($languages, static fn(string $a, string $b) => $a <=> $b);

        /**
         * @var list<array{
         *     branch: string,
         *     translations: array<string, int>,
         *     approvals: array<string, int>,
         *     extensions?: array<string, array{
         *         translations: array<string, int>,
         *         approvals: array<string, int>,
         *     }>
         * }> $branches
         */
        $branches = array_map(
            static fn(BranchTranslationProgress $translationProgressByBranch): array => $translationProgressByBranch->toArray(),
            $translationProgressesByBranch,
        );

        for ($i = 0, $count = count($branches); $i < $count; $i++) {
            /** @var list<DirectoryTranslationProgress> $translationProgressByDirectories */
            $translationProgressByDirectories = array_filter(
                $translationProgressesByDirectory,
                static fn(DirectoryTranslationProgress $translationProgressByDirectory): bool => $translationProgressByDirectory->branch->name === $branches[$i]['branch']
            ) |> array_values(...);

            foreach ($translationProgressByDirectories as $translationProgressByDirectory) {
                $branches[$i]['extensions'][$translationProgressByDirectory->directory->name] = $translationProgressByDirectory->translationProgressToArray();
            }
        }

        $result = [
            'date' => $this->clock->now()->format(\DateTimeInterface::ATOM),
            'languages' => $languages,
            'branches' => $branches,
        ];

        if (@file_put_contents($filePath, json_encode($result, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)) === false) {
            throw StatusProcessingException::fromJsonContentNotWritable($filePath);
        }
    }
}
