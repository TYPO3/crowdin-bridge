<?php

declare(strict_types=1);


namespace App\Tests\Status\Overview;

use App\Configuration\Language;
use App\Configuration\Project;
use App\Status\Overview\ProjectTranslationProgress;
use App\Status\Overview\ProjectTranslationProgressCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProjectTranslationProgressCollection::class)]
final class ProjectTranslationProgressCollectionTest extends TestCase
{
    private ProjectTranslationProgressCollection $subject;

    protected function setUp(): void
    {
        $this->subject = new ProjectTranslationProgressCollection();
    }

    #[Test]
    public function iteratingReturnsNoResultsIfNoProjectsWereAdded(): void
    {
        $actual = iterator_to_array($this->subject);

        self::assertCount(0, $actual);
    }

    #[Test]
    public function addAddsProjectsAndIteratingOverThemWorksCorrectly(): void
    {
        $project1 = new ProjectTranslationProgress(
            new Project(1, 'typo3-extension-project1', 'project1', []),
            []
        );
        $project2 = new ProjectTranslationProgress(
            new Project(2, 'typo3-extension-project2', 'project2', []),
            []
        );

        $this->subject->add($project1);
        $this->subject->add($project2);

        $actual = iterator_to_array($this->subject);

        self::assertCount(2, $actual);
        self::assertSame($project1, $actual[0]);
        self::assertSame($project2, $actual[1]);
    }

    #[Test]
    public function getCoreLanguagesReturnsEmptyArrayWhenCoreProjectIsNotFound(): void
    {
        $actual = $this->subject->getCoreLanguages();

        self::assertCount(0, $actual);
    }

    #[Test]
    public function getCoreLanguagesReturnsLanguagesCorrectly(): void
    {
        $project1 = new ProjectTranslationProgress(
            new Project(1, 'typo3-extension-project1', 'project1', []),
            []
        );
        $project2 = new ProjectTranslationProgress(
            new Project(2, 'typo3-cms', 'project2', [
                new Language('de', 'German'),
                new Language('fr', 'French'),
                new Language('it', 'Italian'),
            ]),
            []
        );

        $this->subject->add($project1);
        $this->subject->add($project2);

        $actual = $this->subject->getCoreLanguages();

        self::assertCount(3, $actual);
        self::assertSame('de', $actual[0]->id);
        self::assertSame('fr', $actual[1]->id);
        self::assertSame('it', $actual[2]->id);
    }
}
