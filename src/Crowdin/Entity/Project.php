<?php

declare(strict_types=1);

namespace App\Crowdin\Entity;

use CrowdinApiClient\Model\Project as CrowdinProject;

final readonly class Project
{
    public function __construct(
        public int $id,
        public string $name,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
        public \DateTimeImmutable $lastActivity,
    ) {}

    public static function fromCrowdinProject(CrowdinProject $project): self
    {
        return new self(
            $project->getId(),
            $project->getName(),
            new \DateTimeImmutable($project->getCreatedAt()),
            new \DateTimeImmutable($project->getUpdatedAt()),
            new \DateTimeImmutable($project->getLastActivity()),
        );
    }
}
