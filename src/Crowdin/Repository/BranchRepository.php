<?php

declare(strict_types=1);

namespace App\Crowdin\Repository;

use App\Crowdin\Entity\Branch;
use App\Sorting\BranchesSorter;
use CrowdinApiClient\Crowdin;
use CrowdinApiClient\Model\Branch as CrowdinBranch;

readonly class BranchRepository
{
    public function __construct(
        private BranchesSorter $branchesSorter,
        private Crowdin $client
    ) {}

    /**
     * @return list<Branch>
     */
    public function list(int $projectId): array
    {
        return array_map(
            static fn(CrowdinBranch $crowdinBranch): Branch => Branch::fromCrowdinBranch($crowdinBranch),
            iterator_to_array($this->client->branch->list($projectId))
        )
            |>array_values(...)
            |>$this->branchesSorter->sortByName(...);
    }
}
