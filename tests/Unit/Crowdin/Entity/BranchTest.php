<?php

declare(strict_types=1);


namespace App\Tests\Unit\Crowdin\Entity;

use App\Crowdin\Entity\Branch;
use CrowdinApiClient\Model\Branch as CrowdinBranch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Branch::class)]
final class BranchTest extends TestCase
{
    #[Test]
    public function fromCrowdinBranch(): void
    {
        $crowdinBranch = new CrowdinBranch([
            'id' => 1,
            'projectId' => 42,
            'name' => '14.3',
        ]);

        $actual = Branch::fromCrowdinBranch($crowdinBranch);

        self::assertSame(1, $actual->id);
        self::assertSame(42, $actual->projectId);
        self::assertSame('14.3', $actual->name);
    }
}
