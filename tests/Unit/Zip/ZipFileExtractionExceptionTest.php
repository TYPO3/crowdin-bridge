<?php

declare(strict_types=1);


namespace App\Tests\Unit\Zip;

use App\Zip\ZipFileExtractionException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ZipFileExtractionException::class)]
final class ZipFileExtractionExceptionTest extends TestCase
{
    #[Test]
    public function fromOpenZipFile(): void
    {
        $actual = ZipFileExtractionException::fromOpenZipFile('/some/file.zip');

        self::assertSame('Could not open ZIP file "/some/file.zip"', $actual->getMessage());
        self::assertSame(1566421924, $actual->getCode());
    }

    #[Test]
    public function fromExtractingZipFile(): void
    {
        $actual = ZipFileExtractionException::fromExtractingZipFile('/some/file.zip', '/some/path');

        self::assertSame('Could not extract ZIP file "/some/file.zip" to path "/some/path"', $actual->getMessage());
        self::assertSame(1795291955, $actual->getCode());
    }
}
