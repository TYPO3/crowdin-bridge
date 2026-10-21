<?php

declare(strict_types=1);

namespace App\Crowdin\Entity;

use CrowdinApiClient\Model\Branch as CrowdinBranch;

final readonly class Branch
{
    public function __construct(
        public int $id,
        public int $projectId,
        public string $name,
    ) {}

    public static function fromCrowdinBranch(CrowdinBranch $branch): self
    {
        return new self(
            $branch->getId(),
            $branch->getProjectId(),
            $branch->getName(),
        );
    }
}
