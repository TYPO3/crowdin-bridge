<?php

declare(strict_types=1);

namespace App\Tests\Functional\Crowdin\Repository;

use App\Crowdin\Dto\DownloadFile;
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

    #[Test]
    public function downloadProjectBuildReturnsDownloadFileCorrectly(): void
    {
        self::$server->setResponseOfPath(
            \sprintf('/api/v2/projects/%s/translations/builds/%s/download', self::CORE_PROJECT_ID, 42),
            new Response(
                \file_get_contents(__DIR__ . '/Fixtures/Translation/download-file.json'),
                ['content-type' => 'application/json'],
                200,
            ),
        );

        $actual = $this->subject->downloadProjectBuild(self::CORE_PROJECT_ID, 42);

        self::assertInstanceOf(DownloadFile::class, $actual);
        self::assertSame('https://crowdin-packages.cf-downloads.crowdin.com/90805931/368815/0/pack.zip?response-content-disposition=attachment%3B%20filename%3D%22TYPO3%20CMS.zip%22&X-Amz-Content-Sha256=UNSIGNED-PAYLOAD&X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Credential=AKIAWVY4ALJ25LH4GC7S%2F20261121%2Fus-east-1%2Fs3%2Faws4_request&X-Amz-Date=20261121T181417Z&X-Amz-SignedHeaders=host&X-Amz-Expires=120&X-Amz-Signature=26d1a5052af478021dc7603a589375bc6668315b6570a4c624aeed8f50b8150f', $actual->url->toAsciiString());
    }

    #[Test]
    public function downloadProjectBuildReturnsNullIfNoDataIsAvailable(): void
    {
        self::$server->setResponseOfPath(
            \sprintf('/api/v2/projects/%s/translations/builds/%s/download', self::CORE_PROJECT_ID, 42),
            new Response(
                \file_get_contents(__DIR__ . '/Fixtures/Translation/download-file-null.json'),
                ['content-type' => 'application/json'],
                200,
            ),
        );

        $actual = $this->subject->downloadProjectBuild(self::CORE_PROJECT_ID, 42);

        self::assertNull($actual);
    }

    #[Test]
    public function downloadProjectBuildWithError(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessageIs('Resource Not Found');

        self::$server->setResponseOfPath(
            \sprintf('/api/v2/projects/%s/translations/builds/%s/download', self::CORE_PROJECT_ID, 42),
            new Response(
                \file_get_contents(__DIR__ . '/Fixtures/Translation/download-file-error.json'),
                ['content-type' => 'application/json'],
                404,
            ),
        );

        $this->subject->downloadProjectBuild(self::CORE_PROJECT_ID, 42);
    }
}
