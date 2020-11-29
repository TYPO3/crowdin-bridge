<?php

declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Service;

class ExtTranslationService extends BaseTranslationServerService
{
    public function getTranslation(string $key, string $language, string $targetBranch)
    {
        $url = $this->getExtensionUrl($key, $language);
        $filePath = $this->downloadPackage($url, $key, $language);
        $absoluteLanguagePath = $this->configurationService->getPathExtracts() . '/transient/crowdin/' . $key . '-l10n-' . $language . '/';

        $this->unzip($filePath, $absoluteLanguagePath);
        $this->processFiles($absoluteLanguagePath);
        $this->upload($absoluteLanguagePath, $language, false, $targetBranch);
    }

    protected function getExtensionUrl(string $key, string $language): string
    {
        return sprintf('https://typo3.org/fileadmin/ter/%s/%s/%s-l10n/%s-l10n-%s.zip',
            $key[0],
            $key[1],
            $key,
            $key,
            $language
        );
    }
}
