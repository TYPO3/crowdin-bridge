<?php

declare(strict_types=1);

namespace App\Zip;

final readonly class ZipFileExtractor
{
    public function extract(string $zipFile, string $pathTo): void
    {
        $zip = new \ZipArchive();
        $openResult = $zip->open($zipFile);
        if ($openResult !== true) {
            throw ZipFileExtractionException::fromOpenZipFile($zipFile);
        }

        $extractionResult = @$zip->extractTo($pathTo);
        if ($extractionResult === false) {
            throw ZipFileExtractionException::fromExtractingZipFile($zipFile, $pathTo);
        }
        $zip->close();
    }
}
