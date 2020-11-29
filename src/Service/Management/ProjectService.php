<?php

declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Service\Management;

use TYPO3\CrowdinBridge\Api\Wrapper\ProjectApi;
use TYPO3\CrowdinBridge\Entity\BridgeConfiguration;
use TYPO3\CrowdinBridge\ExtendedApi\AccountGetProjectsResponse;
use TYPO3\CrowdinBridge\ExtendedApi\UpdateProject\Accounting;
use TYPO3\CrowdinBridge\Utility\FileHandling;

class ProjectService
{

    public function updateConfiguration(): AccountGetProjectsResponse
    {
        // initialize with false to create new configuration file
        $bridgeConfiguration = new BridgeConfiguration(false);
        $bridgeConfiguration->getPathRsync(); // just call to avoid false positive no usage

        $projectApi = new ProjectApi();
        $response = new AccountGetProjectsResponse();

        $fileConfiguration = $projectApi->getConfiguration();
        foreach ($projectApi->getAll() as $remoteProject) {
            $identifier = $remoteProject->getIdentifier();

            $remoteLanguages = $remoteProject->getTargetLanguageIds();
            sort($remoteLanguages);

            $key = $this->generateExtensionKey($identifier, $remoteProject->getName());
            $newData = [
                'id' => $remoteProject->getId(),
                'extensionKey' => $key,
                'languages' => implode(',', $remoteLanguages)
            ];
            $fileConfiguration->add($identifier, $newData);
            $response->addNewProject($key);
        }
        return $response;
    }

    protected function generateExtensionKey(string $identifier, string $name)
    {
        if ($identifier === 'typo3-cms') {
            return $identifier;
        }
        if (FileHandling::beginsWith($identifier, 'typo3-extension-')) {

            return trim(str_replace('typo3 extension', '', strtolower($name)));
        }
        throw new \UnexpectedValueException(sprintf('Identifier "%s" not allowed!', $identifier));
    }
}
