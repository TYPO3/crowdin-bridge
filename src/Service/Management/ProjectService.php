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
        return $this->getApiResponse($accountKey, $username);
    }

    public function updateConfiguration(string $accountKey, string $username): AccountGetProjectsResponse
    {
        $existingProjects = $this->configurationService->getAllProjects();
        $data = $this->getApiResponse($accountKey, $username);
        $response = new AccountGetProjectsResponse();
        foreach ($data as $project) {
            $identifier = $project['identifier'];

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

            $data = json_decode($content, true);
            $data = $data['projects'];
            usort($data, static function ($a, $b) {
                return strcmp(strtolower($a['name']), strtolower($b['name']));
            });

            $allProjects = [];
            foreach ($data as $project) {
                if (in_array($project['identifier'], self::SKIPPED_PROJECTS, true)) {
                    continue;
                }
                $allProjects[] = $project;
            }
            return $allProjects;
        }

        return [];
    }

}
