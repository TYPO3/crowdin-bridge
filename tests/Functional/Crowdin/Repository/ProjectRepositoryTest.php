<?php

declare(strict_types=1);


namespace App\Tests\Functional\Crowdin\Repository;

use App\Crowdin\Entity\Project;
use App\Crowdin\Repository\ProjectRepository;
use App\Crowdin\Repository\RepositoryException;
use donatj\MockWebServer\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ProjectRepository::class)]
final class ProjectRepositoryTest extends RepositoryTestCase
{
    private const int SCHEMA_PROJECT_ID = 391559;

    private ProjectRepository $subject;

    protected function setUp(): void
    {
        $this->subject = new ProjectRepository(self::$client);
    }

    #[Test]
    public function findByIdReturnsProjectCorrectly(): void
    {
        self::$server->setResponseOfPath(
            \sprintf('/api/v2/projects/%d', self::SCHEMA_PROJECT_ID),
            new Response(
                \file_get_contents(__DIR__ . '/Fixtures/Project/project.json'),
                ['content-type' => 'application/json'],
                200,
            ),
        );

        $actual = $this->subject->findById(self::SCHEMA_PROJECT_ID);

        self::assertInstanceOf(Project::class, $actual);
        self::assertSame(self::SCHEMA_PROJECT_ID, $actual->id);
        self::assertSame('TYPO3 Extension schema', $actual->name);
    }

    #[Test]
    public function findByIdReturnsNullIfProjectDoesNotExist(): void
    {
        self::$server->setResponseOfPath(
            \sprintf('/api/v2/projects/%d', self::SCHEMA_PROJECT_ID),
            new Response(
                '{}',
                ['content-type' => 'application/json'],
                200,
            ),
        );

        $actual = $this->subject->findById(self::SCHEMA_PROJECT_ID);

        self::assertNull($actual);
    }

    #[Test]
    public function findByIdThrowsExceptionIfProjectIsNotFound(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessageIs('Resource Not Found');

        self::$server->setResponseOfPath(
            \sprintf('/api/v2/projects/%d', 42),
            new Response(
                <<<BODY
{
    "error": {
        "message": "Resource Not Found",
        "code": 404
    }
}
BODY,
                ['content-type' => 'application/json'],
                404,
            ),
        );

        $this->subject->findById(42);
    }
}
