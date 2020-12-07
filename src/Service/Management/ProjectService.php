<?php

declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Service\Management;

use TYPO3\CrowdinBridge\Api\Wrapper\LanguageApi;
use TYPO3\CrowdinBridge\Api\Wrapper\ProjectApi;
use TYPO3\CrowdinBridge\Entity\BridgeConfiguration;
use TYPO3\CrowdinBridge\Utility\FileHandling;

class ProjectService
{

    public function updateConfiguration(): array
    {
        $projects = [];
        // initialize with false to create new configuration file
        $bridgeConfiguration = new BridgeConfiguration(false);
        $bridgeConfiguration->getPathRsync(); // just call to avoid false positive no usage

        $projectApi = new ProjectApi();

        $fileConfiguration = $projectApi->getConfiguration();
        foreach ($projectApi->getAll() as $remoteProject) {
            $identifier = $remoteProject->getIdentifier();

            $remoteLanguages = $remoteProject->getTargetLanguageIds();

            // add custom language t3
            $languageMapping = $remoteProject->getInContextPseudoLanguage();
            if ($languageMapping['id'] ?? '' === 't3') {
                $remoteLanguages[] = 't3';
            }

            sort($remoteLanguages);

            $key = $this->generateExtensionKey($identifier, $remoteProject->getName());
            $newData = [
                'id' => $remoteProject->getId(),
                'extensionKey' => $key,
                'languages' => implode(',', $remoteLanguages)
            ];
            $fileConfiguration->add($identifier, $newData);
            $projects[] = $key;
        }

        $this->updateLanguages();

        return $projects;
    }

    /**
     * Fill assets/languages.json with detailed language information
     * As this information won't change that much, it is fine to save it in files
     * + it will be updated during running setup anyway
     */
    protected function updateLanguages(): void
    {
        $collectedLanguages = [];
        $languageApi = new LanguageApi();
        $allLanguages = $languageApi->get();

        foreach ($allLanguages as $language) {
            $collectedLanguages[$language->getId()] = $language->getData();
        }
        $file = __DIR__ . '/../../../assets/languages.json';
        file_put_contents($file, json_encode($collectedLanguages, JSON_PRETTY_PRINT));
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
