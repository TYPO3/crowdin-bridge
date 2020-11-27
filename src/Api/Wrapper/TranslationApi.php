<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Api\Wrapper;

use CrowdinApiClient\Model\TranslationProjectBuild;
use TYPO3\CrowdinBridge\Api\Client;

class TranslationApi extends Client
{

    public function buildProject(string $projectIdentifier): ?TranslationProjectBuild
    {
        $projectConfiguration = $this->configuration->getProject($projectIdentifier);

        $params = [
            'skipUntranslatedStrings' => true
        ];
        return $this->client->translation->buildProject($projectConfiguration->getId(), $params);
    }

}
