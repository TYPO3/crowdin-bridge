<?php

declare(strict_types=1);


namespace App\Tests\Unit\Zip;

use App\Zip\ZipFileExtractionException;
use App\Zip\ZipFileExtractor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ZipFileExtractor::class)]
final class ZipFileExtractorTest extends TestCase
{
    private ZipFileExtractor $subject;

    protected function setUp(): void
    {
        $this->subject = new ZipFileExtractor();
    }

    #[Test]
    public function extractExtractsZipFileCorrectly(): void
    {
        $extractionPath = sys_get_temp_dir() . '/crowdinbridge_zipfileextractortest_' . bin2hex(random_bytes(12));

        $this->subject->extract(__DIR__ . '/Fixtures/zipfileextractortest.zip', $extractionPath);

        self::assertFileExists($extractionPath . '/test.txt');
    }

    #[Test]
    public function extractThrowsExceptionIfZipFileDoesNotExist(): void
    {
        $nonExistingZipFile = __DIR__ . '/nonexisting.zip';
        $extractionPath = sys_get_temp_dir() . '/crowdinbridge_zipfileextractortest_' . bin2hex(random_bytes(12));

        $this->expectException(ZipFileExtractionException::class);
        $this->expectExceptionCode(1566421924);

        $this->subject->extract($nonExistingZipFile, $extractionPath);
    }

    #[Test]
    public function extractReturnsFalseIfPathToIsNotWritable(): void
    {
        $extractionPath = sys_get_temp_dir() . '/crowdinbridge_zipfileextractortest_' . bin2hex(random_bytes(12));
        mkdir($extractionPath, 0555);

        $this->expectException(ZipFileExtractionException::class);
        $this->expectExceptionCode(1795291955);

        $this->subject->extract(__DIR__ . '/Fixtures/zipfileextractortest.zip', $extractionPath);
    }
}
