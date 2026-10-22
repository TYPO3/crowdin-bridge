<?php

declare(strict_types=1);


namespace App\Tests\Crowdin\Entity;

use App\Crowdin\Entity\Directory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use CrowdinApiClient\Model\Directory as CrowdinDirectory;

#[CoversClass(Directory::class)]
final class DirectoryTest extends TestCase
{
    #[Test]
    public function fromCrowdinDirectory(): void
    {
        $crowdinDirectory = new CrowdinDirectory([
            'id' => 1,
            'projectId' => 42,
            'branchId' => 100,
            'directoryId' => 200,
            'name' => 'adminpanel',
            'path' => '/14.3/typo3/sysext/adminpanel',
        ]);

        $actual = Directory::fromCrowdinDirectory($crowdinDirectory);

        self::assertSame(1, $actual->id);
        self::assertSame(42, $actual->projectId);
        self::assertSame(100, $actual->branchId);
        self::assertSame(200, $actual->directoryId);
        self::assertSame('adminpanel', $actual->name);
        self::assertSame('/14.3/typo3/sysext/adminpanel', $actual->path);
    }
}
