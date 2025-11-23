<?php

declare(strict_types=1);


namespace App\Tests\Crowdin\Entity;

use App\Crowdin\Entity\Project;
use CrowdinApiClient\Crowdin;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use CrowdinApiClient\Model\Project as CrowdinProject;

#[CoversClass(Project::class)]
final class ProjectTest extends TestCase
{
    #[Test]
    public function fromCrowdinProject(): void
    {
        $crowdinProject = new CrowdinProject([
            'id' => 1,
            'name' => 'Some name',
            'createdAt' => '2025-01-01T01:02:03+00:00',
            'updatedAt' => '2025-02-02T02:03:04+00:00',
            'lastActivity' => '2025-03-03T03:04:05+00:00',
        ]);

        $actual = Project::fromCrowdinProject($crowdinProject);

        self::assertSame(1, $actual->id);
        self::assertSame('Some name', $actual->name);
        self::assertSame('2025-01-01T01:02:03+00:00', $actual->createdAt->format('c'));
        self::assertSame('2025-02-02T02:03:04+00:00', $actual->updatedAt->format('c'));
        self::assertSame('2025-03-03T03:04:05+00:00', $actual->lastActivity->format('c'));
    }
}
