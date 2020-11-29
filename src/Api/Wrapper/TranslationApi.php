<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Api\Wrapper;

use CrowdinApiClient\Model\DownloadFile;
use CrowdinApiClient\Model\TranslationProjectBuild;
use TYPO3\CrowdinBridge\Api\Client;
use TYPO3\CrowdinBridge\Exception\NoFinishedProjectBuildFoundException;

class TranslationApi extends Client
{

    public function buildProject(string $projectIdentifier): ?TranslationProjectBuild
    {
        $projectConfiguration = $this->configuration->getProject($projectIdentifier);

        $params = [
            'exportApprovedOnly' => true,
            'skipUntranslatedStrings' => true
        ];
        return $this->client->translation->buildProject($projectConfiguration->getId(), $params);
    }


    public function downloadProject(int $projectId, int $buildId): ?DownloadFile
    {
        return $this->client->translation->downloadProjectBuild($projectId, $buildId);
    }

    public function getBuilds(int $projectId)
    {
        $params = [
            'limit' => 10
        ];
        return $this->client->translation->getProjectBuilds($projectId, $params);
    }

    /**
     * @param int $projectId
     * @return int build id
     * @throws NoFinishedProjectBuildFoundException
     */
    public function getLastFinishedBuildId(int $projectId): int
    {
        $params = [
            'limit' => 10
        ];
        $items = $this->client->translation->getProjectBuilds($projectId, $params);
        if (!$items) {
            throw new NoFinishedProjectBuildFoundException(sprintf('No builds found for project "%s"', $projectId));
        }
        foreach ($items as $item) {
            /** @var TranslationProjectBuild $item */
            if ($item->getStatus() === 'finished') {
                return $item->getId();
            }
        }
        throw new NoFinishedProjectBuildFoundException(sprintf('No finished build found for project "%s"', $projectId));

    }

}
