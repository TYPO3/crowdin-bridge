<?php

declare(strict_types=1);

namespace App\Download;

final readonly class ZipExtractor
{
    public function extract(string $zipFile, string $pathTo): void
    {
        $zip = new \ZipArchive();
        $openResult = $zip->open($zipFile);
        if ($openResult !== true) {
            throw ZipExtractionException::fromOpenZipFile($zipFile);
        }

        $extractionResult = @$zip->extractTo($pathTo);
        if ($extractionResult === false) {
            throw ZipExtractionException::fromExtractingZipFile($zipFile, $pathTo);
        }
        $zip->close();
    }
}
