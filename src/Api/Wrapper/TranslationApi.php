<?php

declare(strict_types=1);

namespace App\Api\Wrapper;

use App\Api\Client;
use CrowdinApiClient\Model\DownloadFile;

class TranslationApi extends Client
{
    public function downloadProject(int $projectId, int $buildId): ?DownloadFile
    {
        return $this->client->translation->downloadProjectBuild($projectId, $buildId);
    }
}
