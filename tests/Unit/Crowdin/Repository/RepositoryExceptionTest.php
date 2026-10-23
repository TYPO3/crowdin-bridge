<?php

declare(strict_types=1);


namespace App\Tests\Unit\Crowdin\Repository;

use App\Crowdin\Repository\RepositoryException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(RepositoryException::class)]
final class RepositoryExceptionTest extends TestCase
{
    #[Test]
    public function fromThrowable(): void
    {
        $exception = new \Exception('Some message', 42);

        $actual = RepositoryException::fromThrowable($exception);

        self::assertSame('Some message', $actual->getMessage());
        self::assertSame(42, $actual->getCode());
        self::assertSame($exception, $actual->getPrevious());
    }
}
