<?php

declare(strict_types=1);


namespace App\Tests\Functional\Crowdin\Repository;

use App\Crowdin\Entity\Branch;
use App\Crowdin\Repository\BranchRepository;
use App\Crowdin\Repository\RepositoryException;
use App\Sorting\BranchesSorter;
use CrowdinApiClient\Crowdin;
use donatj\MockWebServer\MockWebServer;
use donatj\MockWebServer\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(BranchRepository::class)]
final class BranchRepositoryTest extends TestCase
{
    protected const int PROJECT_ID = 368815;

    private static MockWebServer $server;
    private static Crowdin $client;
    private BranchRepository $subject;

    public static function setUpBeforeClass(): void
    {
        self::$server = new MockWebServer();
        self::$server->start();

        self::$client = new Crowdin([]);
        self::$client->setBaseUri(self::$server->getServerRoot() . '/api/v2');
    }

    public static function tearDownAfterClass(): void
    {
        self::$server->stop();
    }

    protected function setUp(): void
    {
        $this->subject = new BranchRepository(new BranchesSorter(), self::$client);
    }

    #[Test]
    public function findByProjectIdReturnsListOfBranchesCorrectly(): void
    {
        self::$server->setResponseOfPath(
            \sprintf('/api/v2/projects/%d/branches', self::PROJECT_ID),
            $this->buildResponseWithBranches(self::PROJECT_ID),
        );

        $actual = $this->subject->findByProjectId(self::PROJECT_ID);

        self::assertIsArray($actual);
        self::assertCount(3, $actual);

        self::assertInstanceOf(Branch::class, $actual[0]);
        self::assertSame(656, $actual[0]->id);
        self::assertSame(self::PROJECT_ID, $actual[0]->projectId);
        self::assertSame('main', $actual[0]->name);

        self::assertInstanceOf(Branch::class, $actual[1]);
        self::assertSame(14142, $actual[1]->id);
        self::assertSame(self::PROJECT_ID, $actual[1]->projectId);
        self::assertSame('14.3', $actual[1]->name);

        self::assertInstanceOf(Branch::class, $actual[2]);
        self::assertSame(11982, $actual[2]->id);
        self::assertSame(self::PROJECT_ID, $actual[2]->projectId);
        self::assertSame('13.4', $actual[2]->name);
    }

    #[Test]
    public function findByProjectIdReturnsError(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionCode(503);
        $this->expectExceptionMessageIsOrContains('Service Unavailable');

        self::$server->setResponseOfPath(
            \sprintf('/api/v2/projects/%d/branches', self::PROJECT_ID),
            new Response(
                <<<BODY
{
    "error": {
        "message": "Service Unavailable",
        "code": 503
    }
}
BODY,
                [
                    'content-type' => 'application/json',
                ],
                503
            ),
        );

        $this->subject->findByProjectId(self::PROJECT_ID);
    }

    private function buildResponseWithBranches(int $projectId): Response
    {
        return new Response(
            <<<BODY
{
	"data": [
		{
			"data": {
				"id": 656,
				"projectId": $projectId,
				"name": "main",
				"title": null,
				"createdAt": "2019-08-19T20:34:24+00:00",
				"updatedAt": "2022-01-31T18:56:35+00:00",
				"exportPattern": null,
				"priority": "normal"
			}
		},
		{
			"data": {
				"id": 11982,
				"projectId": $projectId,
				"name": "13.4",
				"title": "typo3 \\/ 13.4",
				"createdAt": "2024-11-12T09:07:07+00:00",
				"updatedAt": "2024-11-12T09:07:07+00:00",
				"exportPattern": null,
				"priority": "normal"
			}
		},
		{
			"data": {
				"id": 14142,
				"projectId": $projectId,
				"name": "14.3",
				"title": "typo3 \\/ 14.3",
				"createdAt": "2026-04-30T12:54:10+00:00",
				"updatedAt": "2026-04-30T12:54:10+00:00",
				"exportPattern": null,
				"priority": "normal"
			}
		}
	],
	"pagination": {
		"offset": 0,
		"limit": 25
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
