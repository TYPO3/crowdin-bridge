<?php

declare(strict_types=1);


namespace App\Tests\Status\Overview;

use App\File\PathResolver;
use App\Status\Overview\PageStatusWriter;
use App\Status\Overview\StatusProcessingException;
use Lcobucci\Clock\FrozenClock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(PageStatusWriter::class)]
final class PageStatusWriterTest extends TestCase
{
    #[Test]
    public function writeWritesHtmlPageCorrectly(): void
    {
        $path = '/tmp/crowdin-brigde-page-status-writer-test-' . uniqid();
        mkdir($path);

        $pageResolverStub = $this->createStub(PathResolver::class);
        $pageResolverStub
            ->method('getTemplatesPath')
            ->willReturn(__DIR__ . '/Fixtures/Templates');
        $pageResolverStub
            ->method('getFrontendPath')
            ->willReturn(__DIR__ . '/Fixtures/Frontend');
        $pageResolverStub
            ->method('getRsyncPath')
            ->willReturn($path);

        $frozenClock = FrozenClock::fromUTC();

        $subject = new PageStatusWriter($frozenClock, $pageResolverStub);
        $subject->write();

        self::assertFileExists($path . '/file.css');
        self::assertFileExists($path . '/status.html');

        $actualStatusPage = file_get_contents($path . '/status.html');
        $expectedBodyContent = 'some content with a date ' . $frozenClock->now()->format(PageStatusWriter::DATE_FORMAT);
        self::assertStringContainsString($expectedBodyContent, $actualStatusPage);
    }

    #[Test]
    public function writeThrowsExceptionIfStatusPageCannotBeWritten(): void
    {
        $this->expectException(StatusProcessingException::class);
        $this->expectExceptionCode(1763293452);

        $path = '/tmp/crowdin-brigde-page-status-writer-test-' . uniqid();
        mkdir($path);
        chmod($path, 600);

        $pageResolverStub = $this->createStub(PathResolver::class);
        $pageResolverStub
            ->method('getTemplatesPath')
            ->willReturn(__DIR__ . '/Fixtures/Templates');
        $pageResolverStub
            ->method('getFrontendPath')
            ->willReturn(__DIR__ . '/Fixtures/Frontend');
        $pageResolverStub
            ->method('getRsyncPath')
            ->willReturn($path);

        $subject = new PageStatusWriter(FrozenClock::fromUTC(), $pageResolverStub);
        $subject->write();
    }
}
