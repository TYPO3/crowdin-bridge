<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\ExtendedApi;

use TYPO3\CrowdinBridge\Configuration\Project;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
class AccountGetProjectsResponse
{

    /** @var Project[] */
    protected $newProjects = [];

    /** @var Project[] */
    protected $updatedProjects = [];

    /**
     * @return Project[]
     */
    public function getNewProjects(): array
    {
        return $this->newProjects;
    }

    /**
     * @return Project[]
     */
    public function getUpdatedProjects(): array
    {
        return $this->updatedProjects;
    }

    public function addUpdatedProject(Project $project): void
    {
        $this->updatedProjects[] = $project;
    }

    public function addNewProject(Project $project): void
    {
        $this->newProjects[] = $project;
    }

    public function noChanges(): bool
    {
        return empty($this->newProjects) && empty($this->updatedProjects);
    }
}
