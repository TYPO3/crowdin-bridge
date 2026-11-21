<?php

declare(strict_types=1);

namespace App\Download;

final class ZipExtractionException extends \RuntimeException
{
    public static function fromOpenZipFile(string $zipFile): self
    {
        return new self(
            sprintf('Could not open ZIP file "%s"', $zipFile),
            1566421924
        );
    }

    public static function fromExtractingZipFile(string $zipFile, string $pathTo): self
    {
        return new self(
            sprintf(
                'Could not extract ZIP file "%s" to path "%s"',
                $zipFile,
                $pathTo,
            ),
            1795291955,
        );
    }
}
