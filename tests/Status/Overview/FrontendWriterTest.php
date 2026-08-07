<?php

declare(strict_types=1);


namespace App\Tests\Status\Overview;

use App\File\PathResolver;
use App\Status\Overview\FrontendWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FrontendWriter::class)]
final class FrontendWriterTest extends TestCase
{
    #[Test]
    public function writeWritesFrontendFilesCorrectly(): void
    {
        $path = '/tmp/crowdin-brigde-frontend-writer-test-' . uniqid();
        mkdir($path);

        $pageResolverStub = $this->createStub(PathResolver::class);
        $pageResolverStub
            ->method('getFrontendPath')
            ->willReturn(__DIR__ . '/Fixtures/Frontend');
        $pageResolverStub
            ->method('getRsyncPath')
            ->willReturn($path);

        $subject = new FrontendWriter($pageResolverStub);
        $subject->write();

        self::assertFileExists($path . '/assets/css/main.css');
        self::assertFileExists($path . '/status.html');
    }
}
