<?php

declare(strict_types=1);

namespace App\Configuration;

/**
 * @implements \IteratorAggregate<int, Project>
 */
final readonly class ProjectCollection implements \Countable, \IteratorAggregate
{
    /**
     * @var list<Project> $projects
     */
    private array $projects;

    public function __construct(Project ...$projects)
    {
        $this->projects = array_values($projects);
    }

    public function findByIdentifier(string $identifier): ?Project
    {
        return $this->projects
            |> (static fn(array $projects) => array_filter($projects, static fn(Project $project): bool => $project->identifier === $identifier))
            |> array_first(...);
    }

    public function count(): int
    {
        return count($this->projects);
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->projects);
    }
}
