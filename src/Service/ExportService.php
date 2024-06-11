<?php

declare(strict_types=1);

namespace App\Service;

use CrowdinApiClient\Model\TranslationProjectBuild;
use App\Api\Wrapper\TranslationApi;

readonly class ExportService
{
    public function __construct(
        protected readonly TranslationApi $translationApi
    )
    {
    }

    public function export(string $projectIdentifier): ?TranslationProjectBuild
    {
        return $this->translationApi->buildProject($projectIdentifier);
    }
}
