<?php

declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Service\Management;

use TYPO3\CrowdinBridge\ExtendedApi\AccountGetProjects;
use TYPO3\CrowdinBridge\ExtendedApi\AccountGetProjectsResponse;
use TYPO3\CrowdinBridge\ExtendedApi\UpdateProject\Accounting;
use TYPO3\CrowdinBridge\Service\BaseService;

class ProjectService extends BaseService
{
    public const SKIPPED_PROJECTS = ['crowdin-playground-typo3'];

    public function getAllProjects(string $accountKey, string $username): array
    {
        $data = $this->getApiResponse($accountKey, $username);
        return $data['projects'] ?? [];
    }

    public function updateConfiguration(string $accountKey, string $username): AccountGetProjectsResponse
    {
        $existingProjects = $this->configurationService->getAllProjects();
        $data = $this->getApiResponse($accountKey, $username);
        $response = new AccountGetProjectsResponse();
        if (isset($data['projects'])) {
            foreach ($data['projects'] as $project) {
                $identifier = $project['identifier'];
                if (in_array($identifier, self::SKIPPED_PROJECTS, true)) {
                    continue;
                }

                if (!isset($existingProjects[$identifier])) {
                    $newProject = $this->configurationService->add($identifier, $project['key'], '');
                    $response->addNewProject($newProject);
                } else {
                    // check if key is the same
                    if ($existingProjects[$identifier]->getKey() !== $project['key']) {
                        $response->addUpdatedProject($existingProjects[$identifier]);
                        $this->configurationService->updateSingleConfiguration($identifier, 'key', $project['key']);
                    }
                }
            }
        }

        return $response;
    }

    protected function getApiResponse(string $accountKey, string $username): array
    {
        $api = new AccountGetProjects($this->client);
        $api
            ->setAccountKey($accountKey)
            ->setUsername($username);
        $apiResponse = $api->execute();

        $response = new AccountGetProjectsResponse();

        if ($content = $apiResponse->getContents()) {
            return json_decode($content, true);
        }

        return [];
    }

}
