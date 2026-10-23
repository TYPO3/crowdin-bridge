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
            \sprintf('/api/v2/projects/%d/translations/builds', self::PROJECT_ID),
            $this->buildResponseForInProgressStatus(self::PROJECT_ID),
        );

        $actual = $this->subject->buildProject(self::PROJECT_ID);

        self::assertSame(TranslationProjectBuildStatus::InProgress, $actual->status);
    }

    #[Test]
    public function buildProjectForStatusWithError(): void
    {
        self::$server->setResponseOfPath(
            \sprintf('/api/v2/projects/%d/translations/builds', self::PROJECT_ID),
            $this->buildResponseForFinishedStatus(self::PROJECT_ID),
        );

        $actual = $this->subject->buildProject(self::PROJECT_ID);

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
            \sprintf('/api/v2/projects/%d/translations/builds', self::PROJECT_ID),
            new Response('{}', ['content-type' => 'application/json'], 200)
        );

        $this->subject->buildProject(self::PROJECT_ID);
    }

    #[Test]
    public function buildProjectWithUnsuccessfulResponseThrowsException(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionCode(503);
        $this->expectExceptionMessageIs('Service Unavailable');

        self::$server->setResponseOfPath(
            \sprintf('/api/v2/projects/%d/translations/builds', self::PROJECT_ID),
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

        $this->subject->buildProject(self::PROJECT_ID);
    }

    private function buildResponseForInProgressStatus(int $projectId): Response
    {
        return new Response(
            <<<BODY
{
	"data": {
		"id": 1285481,
		"projectId": $projectId,
		"status": "inProgress",
		"progress": 0,
		"createdAt": "2026-10-23T14:53:48+00:00",
		"updatedAt": "2026-10-23T14:53:48+00:00",
		"finishedAt": null,
		"attributes": {
			"branchId": null,
			"directoryId": null,
			"targetLanguageIds": [],
			"skipUntranslatedStrings": true,
			"skipUntranslatedFiles": false,
			"exportApprovedOnly": true
		}
	}
}
BODY,
            [
                'content-type' => 'application/json',
            ],
            200,
        );
    }

    private function buildResponseForFinishedStatus(int $projectId): Response
    {
        return new Response(
            <<<BODY
{
	"data": {
		"id": 1285481,
		"projectId": $projectId,
		"status": "finished",
		"progress": 0,
		"createdAt": "2026-10-23T14:53:48+00:00",
		"updatedAt": "2026-10-23T14:53:48+00:00",
		"finishedAt": "2026-10-23T14:53:48+00:00",
		"error": {
		    "message": "Export failed: the exported file contains invalid data."
		},
		"attributes": {
			"branchId": null,
			"directoryId": null,
			"targetLanguageIds": [],
			"skipUntranslatedStrings": true,
			"skipUntranslatedFiles": false,
			"exportApprovedOnly": true
		}
	}
}
BODY,
            [
                'content-type' => 'application/json',
            ],
            200,
        );
    }
}
