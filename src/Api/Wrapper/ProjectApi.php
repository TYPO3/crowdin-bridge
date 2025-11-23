<?php

declare(strict_types=1);

namespace App\Api\Wrapper;

use App\Api\Client;
use CrowdinApiClient\Model\Progress;
use CrowdinApiClient\Model\Project;

class ProjectApi extends Client
{
    public const array SKIPPED_PROJECTS = ['crowdin-playground-typo3', 'playground-trados'];

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
            $projects[$item->getIdentifier()] = $item;
        }
        ksort($projects);
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
     * @param int $projectId
     * @return Progress[]
     * @throws \App\Exception\NoApiCredentialsException
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
