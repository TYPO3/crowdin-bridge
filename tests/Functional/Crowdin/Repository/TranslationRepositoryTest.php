<?php

declare(strict_types=1);


namespace App\Tests\Functional\Crowdin\Repository;

use App\Crowdin\Enum\TranslationProjectBuildStatus;
use App\Crowdin\Repository\RepositoryException;
use App\Crowdin\Repository\TranslationRepository;
use donatj\MockWebServer\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(TranslationRepository::class)]
final class TranslationRepositoryTest extends RepositoryTestCase
{
    private TranslationRepository $subject;

    protected function setUp(): void
    {
        $this->subject = new TranslationRepository(self::$client);
    }

    public function buildProjectForStatusWithoutError(): void
    {
        self::$server->setResponseOfPath(
            \sprintf('/api/v2/projects/%d/translations/builds', self::CORE_PROJECT_ID),
            new Response(
                \file_get_contents(__DIR__ . '/Fixtures/TranslationStatus/in-progress.json'),
                ['content-type' => 'application/json'],
                200,
            ),
        );

        $actual = $this->subject->buildProject(self::CORE_PROJECT_ID);

        self::assertSame(1285481, $actual->id);
        self::assertSame(TranslationProjectBuildStatus::InProgress, $actual->status);
        self::assertSame('', $actual->error);
    }

    #[Test]
    public function buildProjectForStatusWithError(): void
    {
        self::$server->setResponseOfPath(
            \sprintf('/api/v2/projects/%d/translations/builds', self::CORE_PROJECT_ID),
            new Response(
                \file_get_contents(__DIR__ . '/Fixtures/Translation/finished.json'),
                ['content-type' => 'application/json'],
                200,
            ),
        );

        $actual = $this->subject->buildProject(self::CORE_PROJECT_ID);

        self::assertSame(TranslationProjectBuildStatus::Finished, $actual->status);
        self::assertSame('Export failed: the exported file contains invalid data.', $actual->error);
    }

    #[Test]
    public function buildProjectWithBuildIsNullThrowsException(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessageIs('buildProject returned null');

        self::$server->setResponseOfPath(
            \sprintf('/api/v2/projects/%d/translations/builds', self::CORE_PROJECT_ID),
            new Response('{}', ['content-type' => 'application/json'], 200)
        );

        $this->subject->buildProject(self::CORE_PROJECT_ID);
    }

    #[Test]
    public function buildProjectWithUnsuccessfulResponseThrowsException(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionCode(503);
        $this->expectExceptionMessageIs('Service Unavailable');

        self::$server->setResponseOfPath(
            \sprintf('/api/v2/projects/%d/translations/builds', self::CORE_PROJECT_ID),
            new Response(
                <<<BODY
{
    "error": {
        "message": "Service Unavailable",
        "code": 503
    }
}
BODY,
                ['content-type' => 'application/json'],
                503,
            )
        );

        $this->subject->buildProject(self::CORE_PROJECT_ID);
    }

    #[Test]
    public function getProjectBuildsReturnsBuildsCorrectly(): void
    {
        self::$server->setResponseOfPath(
            \sprintf('/api/v2/projects/%s/translations/builds', self::CORE_PROJECT_ID),
            new Response(
                \file_get_contents(__DIR__ . '/Fixtures/Translation/builds.json'),
                ['content-type' => 'application/json'],
                200,
            ),
        );

        $actual = $this->subject->getProjectBuilds(self::CORE_PROJECT_ID);

        self::assertCount(2, $actual);
        self::assertSame(1285659, $actual[0]->id);
        self::assertSame(TranslationProjectBuildStatus::Finished, $actual[0]->status);
        self::assertSame('', $actual[0]->error);
        self::assertSame(1285660, $actual[1]->id);
        self::assertSame(TranslationProjectBuildStatus::InProgress, $actual[1]->status);
        self::assertSame('', $actual[1]->error);
    }

    #[Test]
    public function getProjectBuildsWithError(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessageIs('Project Not Found');

        self::$server->setResponseOfPath(
            \sprintf('/api/v2/projects/%s/translations/builds', 42),
            new Response(
                \file_get_contents(__DIR__ . '/Fixtures/Translation/builds-error.json'),
                ['content-type' => 'application/json'],
                404,
            ),
        );

        $this->subject->getProjectBuilds(42);
    }
}
