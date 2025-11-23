<?php

declare(strict_types=1);

namespace App\Crowdin\Repository;

use App\Crowdin\Entity\Project;
use CrowdinApiClient\Crowdin;
use CrowdinApiClient\Model\Project as CrowdinProject;

/**
 * Connect to the Project API of Crowdin
 * @see https://support.crowdin.com/developer/api/v2/#tag/Projects
 */
final readonly class ProjectRepository
{
    public function __construct(
        private Crowdin $client
    ) {}

    public function findById(int $id): ?Project
    {
        $project = $this->client->project->get($id);
        if ($project instanceof CrowdinProject) {
            return Project::fromCrowdinProject($project);
        }

        return null;
    }
}
