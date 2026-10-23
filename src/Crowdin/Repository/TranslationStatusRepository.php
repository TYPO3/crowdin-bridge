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
     * @link https://support.crowdin.com/developer/api/v2/#tag/Translation-Status/operation/api.projects.languages.progress.getMany Documentation
     */
    public function findByProjectId(int $projectId): array
    {
        try {
            return iterator_to_array($this->client->translationStatus->getProjectProgress($projectId, self::DEFAULT_PARAMS) ?? [])
                    |> (static fn(array $progresses) => array_map(static fn(Progress $progress): TranslationProgress => TranslationProgress::fromCrowdinProgress($progress), $progresses))
                    |> array_values(...);
        } catch (\Throwable $t) {
            throw RepositoryException::fromThrowable($t);
        }
    }

    /**
     * @return list<TranslationProgress>
     * @link https://support.crowdin.com/developer/api/v2/#tag/Translation-Status/operation/api.projects.branches.languages.progress.getMany Documentation
     */
    public function findByBranchId(int $projectId, int $branchId): array
    {
        try {
            return iterator_to_array($this->client->translationStatus->getBranchProgress($projectId, $branchId, self::DEFAULT_PARAMS) ?? [])
                    |> (static fn(array $progresses) => array_map(static fn(Progress $progress): TranslationProgress => TranslationProgress::fromCrowdinProgress($progress), $progresses))
                    |> array_values(...);
        } catch (\Throwable $t) {
            throw RepositoryException::fromThrowable($t);
        }
    }

    /**
     * @return list<TranslationProgress>
     * @link https://support.crowdin.com/developer/api/v2/#tag/Translation-Status/operation/api.projects.directories.languages.progress.getMany Documentation
     */
    public function findByDirectoryId(int $projectId, int $directoryId): array
    {
        try {
            return iterator_to_array($this->client->translationStatus->getDirectoryProgress($projectId, $directoryId, self::DEFAULT_PARAMS) ?? [])
                    |> (static fn(array $progresses) => array_map(static fn(Progress $progress): TranslationProgress => TranslationProgress::fromCrowdinProgress($progress), $progresses))
                    |> array_values(...);
        } catch (\Throwable $t) {
            throw RepositoryException::fromThrowable($t);
        }
    }
}
