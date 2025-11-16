<?php

declare(strict_types=1);


namespace App\Tests\Status\Overview;

use App\Status\Overview\StatusProcessingException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(StatusProcessingException::class)]
final class StatusProcessingExceptionTest extends TestCase
{
    #[Test]
    public function fromMissingPageTemplateFile(): void
    {
        $actual = StatusProcessingException::fromMissingPageTemplateFile('/some/path');

        self::assertSame('Template with path "/some/path" cannot be read.', $actual->getMessage());
        self::assertSame(1763293451, $actual->getCode());
    }

    #[Test]
    public function fromPageContentNotWritable(): void
    {
        $actual = StatusProcessingException::fromPageContentNotWritable('/some/path');

        self::assertSame('Page with path "/some/path" cannot be written.', $actual->getMessage());
        self::assertSame(1763293452, $actual->getCode());
    }

    #[Test]
    public function fromJsonContentNotWritable(): void
    {
        $actual = StatusProcessingException::fromJsonContentNotWritable('/some/path');

        self::assertSame('JSON with path "/some/path" cannot be written.', $actual->getMessage());
        self::assertSame(1763293453, $actual->getCode());
    }
}
