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
     * @link https://support.crowdin.com/developer/api/v2/#tag/Source-Files/operation/api.projects.branches.getMany Documentation
     */
    public function findByProjectId(int $projectId): array
    {
        try {
            return array_map(
                static fn(CrowdinBranch $crowdinBranch): Branch => Branch::fromCrowdinBranch($crowdinBranch),
                iterator_to_array($this->client->branch->list($projectId))
            )
                    |> array_values(...)
                    |> $this->branchesSorter->sortByName(...);
        } catch (\Throwable $t) {
            throw RepositoryException::fromThrowable($t);
        }
    }
}
