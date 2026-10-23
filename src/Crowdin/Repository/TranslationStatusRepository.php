<?php

declare(strict_types=1);

namespace App\Crowdin\Repository;

use App\Crowdin\Dto\TranslationProgress;
use CrowdinApiClient\Crowdin;
use CrowdinApiClient\Model\Progress;

/**
 * Connects to the TranslationStatus API of the Crowdin Client
 * @see https://support.crowdin.com/developer/api/v2/#tag/Translation-Status
 */
readonly class TranslationStatusRepository
{
    private const array DEFAULT_PARAMS = [
        'limit' => 500,
    ];

    public function __construct(
        private Crowdin $client
    ) {}

    /**
     * @return list<TranslationProgress>
     */
    public function findByProjectId(int $projectId): array
    {
        return iterator_to_array($this->client->translationStatus->getProjectProgress($projectId, self::DEFAULT_PARAMS) ?? [])
            |> (static fn(array $progresses) => array_map(static fn(Progress $progress): TranslationProgress => TranslationProgress::fromCrowdinProgress($progress), $progresses))
            |> array_values(...);
    }

    /**
     * @return list<TranslationProgress>
     */
    public function findByBranchId(int $projectId, int $branchId): array
    {
        return iterator_to_array($this->client->translationStatus->getBranchProgress($projectId, $branchId, self::DEFAULT_PARAMS) ?? [])
                |> (static fn(array $progresses) => array_map(static fn(Progress $progress): TranslationProgress => TranslationProgress::fromCrowdinProgress($progress), $progresses))
                |> array_values(...);
    }

    /**
     * @return list<TranslationProgress>
     */
    public function findByDirectoryId(int $projectId, int $directoryId): array
    {
        return iterator_to_array($this->client->translationStatus->getDirectoryProgress($projectId, $directoryId, self::DEFAULT_PARAMS) ?? [])
                |> (static fn(array $progresses) => array_map(static fn(Progress $progress): TranslationProgress => TranslationProgress::fromCrowdinProgress($progress), $progresses))
                |> array_values(...);
    }
}
