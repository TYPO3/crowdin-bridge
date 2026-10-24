<?php

declare(strict_types=1);


namespace App\Tests\Unit\Info;

use App\Info\CoreInformation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(CoreInformation::class)]
final class CoreInformationTest extends TestCase
{
    private CoreInformation $subject;

    protected function setUp(): void
    {
        $this->subject = new class extends CoreInformation {
            protected array $versions = [
                15,
                14,
                13,
                12
            ];

            protected array $branchMapping = [
                14 => '14.3',
                13 => '13.4',
                12 => '12.4',
            ];

            protected array $coreExtensions = [
                'adminpanel',
                'backend',
                'core',
                'dashboard',
            ];
        };
    }

    #[Test]
    public function getLatestVersion(): void
    {
        $actual = $this->subject->getLatestVersion();

        self::assertSame(15, $actual);
    }

    #[Test]
    public function getLatestVersionThrowsExceptionIfNotLatestVersionIsAvailable(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionCode(1792838931);
        $this->expectExceptionMessageIs('Latest Core version could not be determined!');

        $subject = new class extends CoreInformation {
            protected array $versions = [];
        };

        $subject->getLatestVersion();
    }

    #[Test]
    #[TestWith(['main', 15])]
    #[TestWith(['14.3', 14])]
    #[TestWith(['13.4', 13])]
    #[TestWith(['12.4', 12])]
    public function getVersionForBranchName(string $branch, int $expected): void
    {
        $actual = $this->subject->getVersionForBranchName($branch);

        self::assertSame($expected, $actual);
    }

    #[Test]
    public function getVersionForBranchNameThrowsExceptionIfBranchIsNotAvailable(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionCode(1567647855);
        $this->expectExceptionMessageIs('Branch "14.4" not found');

        $this->subject->getVersionForBranchName('14.4');
    }

    #[Test]
    public function getAllCoreExtensionKeys(): void
    {
        $actual = $this->subject->getAllCoreExtensionKeys();

        self::assertArraysAreIdentical([
            'adminpanel',
            'backend',
            'core',
            'dashboard',
        ], $actual);
    }

    #[Test]
    public function getAllCoreBranches(): void
    {
        $actual = $this->subject->getAllCoreBranches();

        self::assertArraysAreIdentical([
            '14.3',
            '13.4',
            '12.4',
            'main',
        ], $actual);
    }
}
