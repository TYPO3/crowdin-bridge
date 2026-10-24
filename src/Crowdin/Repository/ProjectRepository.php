<?php

declare(strict_types=1);

namespace App\Crowdin\Repository;

use App\Crowdin\Entity\Project;
use CrowdinApiClient\Crowdin;
use CrowdinApiClient\Model\Project as CrowdinProject;

/**
 * Connect to the Project API of Crowdin
 */
final readonly class ProjectRepository
{
    public function __construct(
        private Crowdin $client
    ) {}

    /**
     * @link https://support.crowdin.com/developer/api/v2/#tag/Projects/operation/api.projects.get Documentation
     */
    public function findById(int $id): ?Project
    {
        try {
            $project = $this->client->project->get($id);
        } catch (\Throwable $t) {
            throw RepositoryException::fromThrowable($t);
        }

        if ($project instanceof CrowdinProject) {
            return Project::fromCrowdinProject($project);
        }

        return null;
    }
}
