<?php

declare(strict_types=1);

namespace App\Service;

use CrowdinApiClient\Model\TranslationProjectBuild;
use App\Api\Wrapper\TranslationApi;

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
