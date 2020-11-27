<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\ExtendedApi;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
class AccountGetProjectsResponse
{

    /** @var string[] */
    protected array $newProjects = [];

    /** @var string[] */
    protected $updatedProjects = [];

    /**
     * @return string[]
     */
    public function getNewProjects(): array
    {
        return $this->newProjects;
    }

    /**
     * @return string[]
     */
    public function getUpdatedProjects(): array
    {
        return $this->updatedProjects;
    }

    public function addUpdatedProject(string $extensionKey): void
    {
        $this->updatedProjects[] = $extensionKey;
    }

    public function addNewProject(string $identifier): void
    {
        $this->newProjects[] = $identifier;
    }

    public function noChanges(): bool
    {
        return empty($this->newProjects) && empty($this->updatedProjects);
    }
}
