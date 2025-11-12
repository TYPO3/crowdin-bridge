<?php

declare(strict_types=1);


namespace App\Tests\Configuration;

use App\Configuration\ProjectCollection;
use FriendsOfTYPO3\CrowdinBase\Configuration\Entity\Project;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProjectCollection::class)]
final class ProjectCollectionTest extends TestCase
{
    private ProjectCollection $subject;

    protected function setUp(): void
    {
        $projects = [
            new Project(1, 'typo3-extension-some-identifier', 'some_identifier', ['de']),
            new Project(2, 'typo3-extension-another-identifier', 'another_identifier', ['it']),
            new Project(3, 'typo3-extension-one-more-identifier', 'one_more_identifier', ['fr']),
        ];

        $this->subject = new ProjectCollection(...$projects);
    }

    #[Test]
    public function findByIdentifierReturnsProjectIfIdentifierIsAvailable(): void
    {
        $actual = $this->subject->findByIdentifier('typo3-extension-another-identifier');

        self::assertInstanceOf(Project::class, $actual);
        self::assertSame(2, $actual->id);
    }

    #[Test]
    public function findByIdentifierReturnsNullIfIdentifierIsNotAvailable(): void
    {
        $actual = $this->subject->findByIdentifier('unknown-identifier');

        self::assertNull($actual);
    }

    #[Test]
    public function countReturnsCorrectNumberOfProjects(): void
    {
        self::assertCount(3, $this->subject);
    }

    #[Test]
    public function iterateOverProjectsIsWorking(): void
    {
        $id = 1;
        foreach ($this->subject as $project) {
            self::assertSame($id, $project->id);
            $id++;
        }
    }
}
