<?php

declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Service;

use Akeneo\Crowdin\Api\Export;

class ExportService extends BaseService
{
    public function export(string $branch = '', bool $async = false)
    {
        /** @var Export $api */
        $api = $this->client->api('export');
        if ($branch) {
            $api->setBranch($branch);
        }
        if ($async || $this->configurationService->isCoreProject()) {
            $api->addUrlParameter('async', 1);
        }
        $api->execute();
    }
}
