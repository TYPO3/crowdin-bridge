<?php

declare(strict_types=1);


namespace App\Tests\Status\Overview;

use App\File\PathResolver;
use App\Status\Overview\JsonStatusWriter;
use App\Status\Overview\StatusProcessingException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonStatusWriter::class)]
final class JsonStatusWriterTest extends TestCase
{
    #[Test]
    public function writeWritesPassedStatusCorrectly(): void
    {
        $path = '/tmp/crowdin-brigde-json-status-writer-test-' . uniqid();
        mkdir($path);

        $pathResolverStub = $this->createStub(PathResolver::class);
        $pathResolverStub
            ->method('getRsyncPath')
            ->willReturn($path);

        $subject = new JsonStatusWriter($pathResolverStub);
        $subject->write(['test' => 'ok']);

        self::assertFileExists($path . '/status.json');
        self::assertJsonStringEqualsJsonString('{"test": "ok"}', file_get_contents($path . '/status.json'));
    }

    #[Test]
    public function writeThrowsExceptionIfStatusFileCannotBeWritten(): void
    {
        $this->expectException(StatusProcessingException::class);
        $this->expectExceptionCode(1763293453);

        $path = '/tmp/crowdin-brigde-json-status-writer-test-' . uniqid();

        $pathResolverStub = $this->createStub(PathResolver::class);
        $pathResolverStub
            ->method('getRsyncPath')
            ->willReturn($path);

        $subject = new JsonStatusWriter($pathResolverStub);
        $subject->write(['test' => 'exception']);
    }
}
