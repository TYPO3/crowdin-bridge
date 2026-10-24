<?php

declare(strict_types=1);


namespace App\Tests\Functional\Crowdin\Repository;

use App\Crowdin\Entity\Directory;
use App\Crowdin\Repository\DirectoryRepository;
use App\Crowdin\Repository\RepositoryException;
use donatj\MockWebServer\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(DirectoryRepository::class)]
final class DirectoryRepositoryTest extends RepositoryTestCase
{
    private DirectoryRepository $subject;

    protected function setUp(): void
    {
        $this->subject = new DirectoryRepository(self::$client);
    }

    #[Test]
    public function findByFilter(): void
    {
        $filter = 'adminpanel';

        self::$server->setResponseOfPath(
            '/api/v2/directories',
            new Response(
                \file_get_contents(__DIR__ . '/Fixtures/Directory/search.json'),
                ['content-type' => 'application/json'],
                200,
            ),
        );

        $actual = $this->subject->findByFilter(self::CORE_PROJECT_ID, $filter);

        self::assertIsArray($actual);
        self::assertCount(3, $actual);

        self::assertInstanceOf(Directory::class, $actual[0]);
        self::assertSame(662, $actual[0]->id);
        self::assertSame(self::CORE_PROJECT_ID, $actual[0]->projectId);
        self::assertSame(656, $actual[0]->branchId);
        self::assertSame(660, $actual[0]->directoryId);
        self::assertSame($filter, $actual[0]->name);
        self::assertSame('/main/typo3/sysext/adminpanel', $actual[0]->path);

        self::assertInstanceOf(Directory::class, $actual[1]);
        self::assertSame(11996, $actual[1]->id);
        self::assertSame(self::CORE_PROJECT_ID, $actual[1]->projectId);
        self::assertSame(11982, $actual[1]->branchId);
        self::assertSame(11994, $actual[1]->directoryId);
        self::assertSame($filter, $actual[1]->name);
        self::assertSame('/13.4/typo3/sysext/adminpanel', $actual[1]->path);

        self::assertInstanceOf(Directory::class, $actual[2]);
        self::assertSame(14148, $actual[2]->id);
        self::assertSame(self::CORE_PROJECT_ID, $actual[2]->projectId);
        self::assertSame(14142, $actual[2]->branchId);
        self::assertSame(14146, $actual[2]->directoryId);
        self::assertSame($filter, $actual[2]->name);
        self::assertSame('/14.3/typo3/sysext/adminpanel', $actual[2]->path);
    }

    #[Test]
    public function findByFilterReturnsError(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionCode(422);
        $this->expectExceptionMessageIsOrContains('The search was too broad to complete within the time limit');

        self::$server->setResponseOfPath(
            '/api/v2/directories',
            new Response(
                <<<BODY
{
    "error": {
        "message": "The search was too broad to complete within the time limit",
        "code": 422
    }
}
BODY,
                [
                    'content-type' => 'application/json',
                ],
                422
            ),
        );

        $this->subject->findByFilter(self::CORE_PROJECT_ID, 'adminpanel');
    }
}
