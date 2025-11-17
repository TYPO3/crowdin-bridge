<?php

declare(strict_types=1);

namespace App\Crowdin\Repository;

use CrowdinApiClient\Crowdin;

/**
 * Connects to the TranslationApi of the Crowdin Client
 */
final readonly class TranslationRepository
{
    public function __construct(
        private Crowdin $client
    ) {}

    /**
     * Builds the given project and returns the status ('finished', 'inProgress', ..., null)
     */
    public function buildProject(int $projectId): ?string
    {
        $params = [
            'exportApprovedOnly' => true,
            'skipUntranslatedStrings' => true,
        ];

        return $this->client->translation->buildProject($projectId, $params)?->getStatus();
    }
}
