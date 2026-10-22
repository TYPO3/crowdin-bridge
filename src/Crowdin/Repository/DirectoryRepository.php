<?php

declare(strict_types=1);

namespace App\Crowdin\Repository;

use App\Crowdin\Entity\Directory;
use CrowdinApiClient\Crowdin;
use CrowdinApiClient\Model\Directory as CrowdinDirectory;

readonly class DirectoryRepository
{
    public function __construct(
        private Crowdin $client
    ) {}

    /**
     * @return list<Directory>
     */
    public function findByFilter(int $projectId, string $filter): array
    {
        $params = [
            'filter' => $filter,
            'projectIds' => $projectId,
        ];

        return array_map(
            static fn(CrowdinDirectory $directory): Directory => Directory::fromCrowdinDirectory($directory),
            iterator_to_array($this->client->directory->search($params))
        )
            |> array_values(...);
    }
}
