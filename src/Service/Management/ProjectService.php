<?php

declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Service\Management;

use TYPO3\CrowdinBridge\ExtendedApi\AccountGetProjects;
use TYPO3\CrowdinBridge\ExtendedApi\AccountGetProjectsResponse;
use TYPO3\CrowdinBridge\ExtendedApi\UpdateProject\Accounting;
use TYPO3\CrowdinBridge\Service\BaseService;
use TYPO3\CrowdinBridge\Service\StatusService as ProjectStatusService;

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

            $singleProjectService = new ProjectStatusService($identifier);
            $singleProjectStatus = $singleProjectService->get();
            $languages = $this->collectAllLanguages($singleProjectStatus);

            if (!isset($existingProjects[$identifier])) {
                $newProject = $this->configurationService->add($identifier, $project['key'], $languages);
                $response->addNewProject($newProject);
            } else {
                $existingProject = $existingProjects[$identifier];
                if ($existingProject->getKey() !== $project['key'] || $existingProject->getLanguages() !== $languages) {
                    $response->addUpdatedProject($existingProjects[$identifier]);
                    $this->configurationService->updateSingleConfiguration($identifier, 'key', $project['key']);
                    $this->configurationService->updateSingleConfiguration($identifier, 'languages', $languages);
                }
            }
        }


        return $response;
    }

    protected function collectAllLanguages(array $projectInfo): string
    {
        $languages = array_column($projectInfo, 'code');

        return implode(',', $languages);
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
