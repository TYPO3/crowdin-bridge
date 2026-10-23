<?php

declare(strict_types=1);


namespace App\Tests\Functional\Crowdin\Repository;

use App\Crowdin\Entity\Branch;
use App\Crowdin\Repository\BranchRepository;
use App\Crowdin\Repository\RepositoryException;
use App\Sorting\BranchesSorter;
use donatj\MockWebServer\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(BranchRepository::class)]
final class BranchRepositoryTest extends RepositoryTestCase
{
    private BranchRepository $subject;

    protected function setUp(): void
    {
        $this->subject = new BranchRepository(new BranchesSorter(), self::$client);
    }

    #[Test]
    public function findByProjectIdReturnsListOfBranchesCorrectly(): void
    {
        self::$server->setResponseOfPath(
            \sprintf('/api/v2/projects/%d/branches', self::PROJECT_ID),
            new Response(
                \file_get_contents(__DIR__ . '/Fixtures/Branch/list.json'),
                ['content-type' => 'application/json'],
                200,
            ),
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
                ['content-type' => 'application/json'],
                503
            ),
        );

        $this->subject->findByProjectId(self::PROJECT_ID);
    }
}
