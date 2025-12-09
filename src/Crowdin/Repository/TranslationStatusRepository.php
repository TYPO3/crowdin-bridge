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
    private const int LIMIT = 100;

    public function __construct(
        private Crowdin $client
    ) {}

    /**
     * @return list<TranslationProgress>
     */
    public function findByProjectId(int $projectId): array
    {
        $params = [
            'limit' => self::LIMIT,
        ];

        return iterator_to_array($this->client->translationStatus->getProjectProgress($projectId, $params) ?? [])
            |> (static fn(array $progresses) => array_map(static fn(Progress $progress): TranslationProgress => TranslationProgress::fromCrowdinProgress($progress), $progresses))
            |> array_values(...);
    }
}
