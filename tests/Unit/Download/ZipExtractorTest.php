<?php

declare(strict_types=1);


namespace App\Tests\Unit\Download;

use App\Download\ZipExtractionException;
use App\Download\ZipExtractor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ZipExtractor::class)]
final class ZipExtractorTest extends TestCase
{
    private ZipExtractor $subject;

    protected function setUp(): void
    {
        $this->subject = new ZipExtractor();
    }

    #[Test]
    public function extractExtractsZipFileCorrectly(): void
    {
        $extractionPath = sys_get_temp_dir() . '/zipextractortest_' . bin2hex(random_bytes(12));

        $this->subject->extract(__DIR__ . '/zipextractortest.zip', $extractionPath);

        self::assertFileExists($extractionPath . '/test.txt');
    }

    #[Test]
    public function extractThrowsExceptionIfZipFileDoesNotExist(): void
    {
        $nonExistingZipFile = __DIR__ . '/nonexisting.zip';
        $extractionPath = sys_get_temp_dir() . '/zipextractortest_' . bin2hex(random_bytes(12));

        $this->expectException(ZipExtractionException::class);
        $this->expectExceptionCode(1566421924);

        $this->subject->extract($nonExistingZipFile, $extractionPath);
    }

    #[Test]
    public function extractReturnsFalseIfPathToIsNotWritable(): void
    {
        $extractionPath = sys_get_temp_dir() . '/zipextractortest_' . bin2hex(random_bytes(12));
        mkdir($extractionPath, 0555);

        $this->expectException(ZipExtractionException::class);
        $this->expectExceptionCode(1795291955);

        $this->subject->extract(__DIR__ . '/zipextractortest.zip', $extractionPath);
    }
}
