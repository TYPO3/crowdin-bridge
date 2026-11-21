<?php

declare(strict_types=1);

namespace App\Crowdin\Dto;

use CrowdinApiClient\Model\DownloadFile as CrowdinDownloadFile;
use Uri\WhatWg\Url;

final readonly class DownloadFile
{
    public function __construct(
        public Url $url,
    ) {}

    public static function fromCrowdinDownloadFile(CrowdinDownloadFile $downloadFile): self
    {
        return new self(
            new Url($downloadFile->getUrl()),
        );
    }
}
