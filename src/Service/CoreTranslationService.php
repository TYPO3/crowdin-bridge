<?php

declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Service;

use TYPO3\CrowdinBridge\Info\CoreInformation;

class CoreTranslationService extends BaseTranslationServerService
{
    public function getTranslation(string $key, string $language, int $version)
    {
        $url = $this->getCoreExtensionUrl($key, $language, $version);
        $filePath = $this->downloadPackage($url, $key, $language, $version);
        $absoluteLanguagePath = $this->configurationService->getPathExtracts() . 'v' . $version . '-' . $key . '-l10n-' . $language . '/';
        $targetBranch = CoreInformation::getBranchNameForVersion($version);

        $this->unzip($filePath, $absoluteLanguagePath);
        $this->processFiles($absoluteLanguagePath);
        $this->upload($absoluteLanguagePath, $language, true, $targetBranch);
    }

    protected function getCoreExtensionUrl(string $key, string $language, int $version): string
    {
        return sprintf('https://typo3.org/fileadmin/ter/%s/%s/%s-l10n/%s-l10n-%s.v%s.zip',
            $key{0},
            $key{1},
            $key,
            $key,
            $language,
            $version
        );
    }
}
