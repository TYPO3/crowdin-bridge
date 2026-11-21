<?php

declare(strict_types=1);


namespace App\Tests\Unit\Download;

use App\Download\ExtensionBranchNameResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(ExtensionBranchNameResolver::class)]
final class ExtensionBranchNameResolverTest extends TestCase
{
    private ExtensionBranchNameResolver $subject;

    protected function setUp(): void
    {
        $this->subject = new ExtensionBranchNameResolver(new NullLogger());
    }

    #[Test]
    public function getBranchNameReturnsBranchNameIfFoundAsConsideredBranchName(): void
    {
        $directory = sys_get_temp_dir() . '/' . bin2hex(random_bytes(12));
        mkdir($directory);
        mkdir($directory . '/develop');
        mkdir($directory . '/master');

        $actual = $this->subject->getBranchName($directory);

        self::assertSame('master', $actual);
    }

    #[Test]
    public function getBranchNameThrowsExceptionIfNoConsideredBranchNameIs(): void
    {
        $directory = sys_get_temp_dir() . '/' . bin2hex(random_bytes(12));
        mkdir($directory);
        mkdir($directory . '/v13');
        mkdir($directory . '/v14');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1566422270);
        $this->expectExceptionMessageIs('Branch not found in directory "' . $directory . '", found: v13, v14');

        $this->subject->getBranchName($directory);
    }
}
