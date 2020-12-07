<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Api\Wrapper;

use CrowdinApiClient\Model\Progress;
use CrowdinApiClient\Model\Project;
use TYPO3\CrowdinBridge\Api\Client;

class ProjectApi extends Client
{

    public const SKIPPED_PROJECTS = ['crowdin-playground-typo3', 'playground-trados'];

    /**
     * @return Project[]
     */
    public function getAll(): array
    {
        $projects = [];
        $items = $this->client->project->list(['limit' => 500]);
        foreach ($items as $item) {
            /** @var Project $item */
            if (in_array($item->getIdentifier(), self::SKIPPED_PROJECTS, true)) {
                continue;
            }
            $projects[] = $item;
        }
        return $projects;
    }

    public function getProject(string $extensionKey): ?Project
    {
        $projectConfiguration = $this->configuration->getProject($extensionKey);

        return $this->getProjectById($projectConfiguration->getId());
    }

    public function getProjectById(int $id): ?Project
    {
        return $this->client->project->get($id);
    }

    /**
     * @param string $projectIdentifier
     * @return Progress[]
     * @throws \TYPO3\CrowdinBridge\Exception\NoApiCredentialsException
     */
    public function getTranslationStatus(string $projectIdentifier): array
    {
        $projectConfiguration = $this->configuration->getProject($projectIdentifier);
        return $this->getTranslationStatusByCrowdinId($projectConfiguration->getId());
    }

    /**
     * @param int $projectId
     * @return Progress[]
     * @throws \TYPO3\CrowdinBridge\Exception\NoApiCredentialsException
     */
    public function getTranslationStatusByCrowdinId(int $projectId): array
    {
        $result = [];
        $params = ['limit' => 100];
        $collection = $this->client->translationStatus->getProjectProgress($projectId, $params);
        if ($collection) {
            foreach ($collection as $item) {
                $result[] = $item;
            }
        }
        return $result;
    }
}
