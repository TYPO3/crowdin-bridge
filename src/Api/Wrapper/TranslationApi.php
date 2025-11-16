<?php

declare(strict_types=1);

namespace App\Api\Wrapper;

use App\Api\Client;
use App\Exception\NoFinishedProjectBuildFoundException;
use CrowdinApiClient\Model\DownloadFile;
use CrowdinApiClient\Model\TranslationProjectBuild;

class TranslationApi extends Client
{
    public function downloadProject(int $projectId, int $buildId): ?DownloadFile
    {
        return $this->client->translation->downloadProjectBuild($projectId, $buildId);
    }

    /**
     * @throws NoFinishedProjectBuildFoundException
     */
    public function getLastFinishedBuildId(int $projectId): int
    {
        $params = [
            'limit' => 10,
        ];
        $items = $this->client->translation->getProjectBuilds($projectId, $params);
        if (count($items) === 0) {
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
