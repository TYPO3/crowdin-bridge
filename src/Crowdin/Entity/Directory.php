<?php

declare(strict_types=1);

namespace App\Crowdin\Entity;

use CrowdinApiClient\Model\Directory as CrowdinDirectory;

final readonly class Directory
{
    public function __construct(
        public int $id,
        public int $projectId,
        public int $branchId,
        public int $directoryId,
        public string $name,
        public string $path,
    ) {}

    public static function fromCrowdinDirectory(CrowdinDirectory $directory): self
    {
        return new self(
            $directory->getId(),
            $directory->getProjectId(),
            $directory->getBranchId(),
            $directory->getDirectoryId(),
            $directory->getName(),
            $directory->getPath(),
        );
    }
}
