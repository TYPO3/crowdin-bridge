<?php

declare(strict_types=1);

namespace App\Service;

use App\Api\Wrapper\TranslationApi;
use CrowdinApiClient\Model\TranslationProjectBuild;

readonly class ExportService
{
    public function __construct(
        protected readonly TranslationApi $translationApi
    ) {}

    public function export(string $projectIdentifier): ?TranslationProjectBuild
    {
        return $this->translationApi->buildProject($projectIdentifier);
    }
}
