<?php

declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Service;

use Akeneo\Crowdin\Api\Export;
use CrowdinApiClient\Model\TranslationProjectBuild;
use TYPO3\CrowdinBridge\Api\Wrapper\TranslationApi;

class ExportService
{
    /** @var TranslationApi */
    protected TranslationApi $translationApi;

    public function __construct()
    {
        $this->translationApi = new TranslationApi();
    }

    public function export(string $projectIdentifier): ?TranslationProjectBuild
    {
        return $this->translationApi->buildProject($projectIdentifier);
    }
}
