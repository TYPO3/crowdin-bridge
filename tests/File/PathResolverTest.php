<?php

declare(strict_types=1);


namespace App\Tests\File;

use App\File\PathResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(PathResolver::class)]
final class PathResolverTest extends TestCase
{
    private PathResolver $subject;

    protected function setUp(): void
    {
        $projectPath = '/tmp/crowdin-bridge-path-resolver-test-' . uniqid();
        mkdir($projectPath);

        $this->subject = new PathResolver($projectPath);
    }

    #[Test]
    public function getDownloadsPathCreatesNonExistingPathCorrectly(): void
    {
        $actual = $this->subject->getDownloadsPath();

        self::assertDirectoryExists($actual);
    }

    #[Test]
    public function getExportPathCreatesNonExistingPathCorrectly(): void
    {
        $actual = $this->subject->getExportPath();

        self::assertDirectoryExists($actual);
    }

    #[Test]
    public function getFinalPathCreatesNonExistingPathCorrectly(): void
    {
        $actual = $this->subject->getFinalPath();

        self::assertDirectoryExists($actual);
    }

    #[Test]
    public function getRsyncPathCreatesNonExistingPathCorrectly(): void
    {
        $actual = $this->subject->getRsyncPath();

        self::assertDirectoryExists($actual);
    }
}
