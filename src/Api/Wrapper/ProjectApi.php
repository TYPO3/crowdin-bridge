<?php

declare(strict_types=1);

namespace App\Api\Wrapper;

use App\Api\Client;
use CrowdinApiClient\Model\Progress;

class ProjectApi extends Client
{
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
