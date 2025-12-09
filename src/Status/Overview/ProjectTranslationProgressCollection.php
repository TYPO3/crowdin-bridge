<?php

declare(strict_types=1);

namespace App\Status\Overview;

use App\Configuration\Language;

/**
 * @implements \IteratorAggregate<int, ProjectTranslationProgress>
 */
final class ProjectTranslationProgressCollection implements \IteratorAggregate
{
    /**
     * @var list<ProjectTranslationProgress>
     */
    private array $projects = [];

    public function add(ProjectTranslationProgress $project): void
    {
        $this->projects[] = $project;
    }

    /**
     * @return list<Language>
     */
    public function getCoreLanguages(): array
    {
        $projectTranslationProgress = $this->projects
            |> (static fn($projects) => array_filter($projects, static fn(ProjectTranslationProgress $projectTranslationProgress): bool => $projectTranslationProgress->project->identifier === 'typo3-cms'))
            |> array_first(...);

        return $projectTranslationProgress?->project->languages ?? [];
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->projects);
    }
}
