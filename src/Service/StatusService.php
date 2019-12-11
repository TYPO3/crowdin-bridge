<?php

declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Service;

use Akeneo\Crowdin\Api\Status;

class StatusService extends BaseService
{
    public function get(): array
    {
        /** @var Status $api */
        $api = $this->client->api('status');
        $api->addUrlParameter('json', 1);
        $response = $api->execute();

        return json_decode($response->getContents(), true);
    }
}
