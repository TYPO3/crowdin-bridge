<?php

declare(strict_types=1);


namespace App\Tests\Configuration;

use App\Configuration\Project;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use FriendsOfTYPO3\CrowdinBase\Configuration\Entity\Language as CrowdinBaseLanguage;
use FriendsOfTYPO3\CrowdinBase\Configuration\Entity\Project as CrowdinBaseProject;

#[CoversClass(Project::class)]
final class ProjectTest extends TestCase
{
    #[Test]
    public function fromCrowdinBaseProject(): void
    {
        $crowdinBaseProject = new CrowdinBaseProject(
            1,
            'some-identifier',
            'some_extension_key',
            [
                new CrowdinBaseLanguage('de', 'German'),
                new CrowdinBaseLanguage('fr', 'French'),
            ]
        );

        $actual = Project::fromCrowdinBaseProject($crowdinBaseProject);

        self::assertSame(1, $actual->id);
        self::assertSame('some-identifier', $actual->identifier);
        self::assertSame('some_extension_key', $actual->extensionKey);
        self::assertCount(2, $actual->languages);
        self::assertSame('de', $actual->languages[0]->id);
        self::assertSame('German', $actual->languages[0]->name);
        self::assertSame('fr', $actual->languages[1]->id);
        self::assertSame('French', $actual->languages[1]->name);
    }

    #[Test]
    public function isCoreProjectReturnsTrueIfCoreProject(): void
    {
        $project = new Project(1, 'typo3-cms', 'core', []);

        self::assertTrue($project->isCoreProject());
    }

    #[Test]
    public function isCoreProjectReturnsFalseIfNotCoreProject(): void
    {
        $project = new Project(1, 'typo3-extension-some-project', 'some_project', []);

        self::assertFalse($project->isCoreProject());
    }
}
