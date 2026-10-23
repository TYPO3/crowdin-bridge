<?php

declare(strict_types=1);


namespace App\Tests\Functional\Crowdin\Repository;

use App\Crowdin\Dto\TranslationProgress;
use App\Crowdin\Repository\RepositoryException;
use App\Crowdin\Repository\TranslationStatusRepository;
use donatj\MockWebServer\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(TranslationStatusRepository::class)]
final class TranslationStatusRepositoryTest extends RepositoryTestCase
{
    private TranslationStatusRepository $subject;

    protected function setUp(): void
    {
        $this->subject = new TranslationStatusRepository(self::$client);
    }

    #[Test]
    public function findByProjectIdReturnsTranslationProgressCorrectly(): void
    {
        self::$server->setResponseOfPath(
            \sprintf('/api/v2/projects/%d/languages/progress', self::PROJECT_ID),
            new Response(
                \file_get_contents(__DIR__ . '/Fixtures/TranslationStatus/project-progress.json'),
                ['content-type' => 'application/json'],
                200,
            ),
        );

        $actual = $this->subject->findByProjectId(self::PROJECT_ID);

        self::assertCount(42, $actual);
        foreach ($actual as $translationProgress) {
            self::assertInstanceOf(TranslationProgress::class, $translationProgress);
        }

        self::assertSame('ar', $actual[0]->languageId);
        self::assertSame(96, $actual[0]->translationProgress);
        self::assertSame(90, $actual[0]->approvalProgress);
        self::assertSame(12473, $actual[0]->phrasesTotal);
        self::assertSame(12091, $actual[0]->phrasesTranslated);
        self::assertSame(12030, $actual[0]->phrasesApproved);
    }

    #[Test]
    public function findByProjectIdThrowsExceptionOnHttpError(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessageIs('Resource Not Found');

        self::$server->setResponseOfPath(
            \sprintf('/api/v2/projects/%d/languages/progress', self::PROJECT_ID),
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

        $this->subject->findByProjectId(self::PROJECT_ID);
    }

    #[Test]
    public function findByBranchIdReturnsTranslationProgressCorrectly(): void
    {
        self::$server->setResponseOfPath(
            \sprintf('/api/v2/projects/%d/branches/%d/languages/progress', self::PROJECT_ID, 42),
            new Response(
                \file_get_contents(__DIR__ . '/Fixtures/TranslationStatus/branch-progress.json'),
                ['content-type' => 'application/json'],
                200,
            ),
        );

        $actual = $this->subject->findByBranchId(self::PROJECT_ID, 42);

        self::assertCount(42, $actual);
        foreach ($actual as $translationProgress) {
            self::assertInstanceOf(TranslationProgress::class, $translationProgress);
        }

        self::assertSame('ar', $actual[0]->languageId);
        self::assertSame(97, $actual[0]->translationProgress);
        self::assertSame(96, $actual[0]->approvalProgress);
        self::assertSame(8150, $actual[0]->phrasesTotal);
        self::assertSame(7867, $actual[0]->phrasesTranslated);
        self::assertSame(7835, $actual[0]->phrasesApproved);
    }

    #[Test]
    public function findByBranchIdThrowsExceptionOnHttpError(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessageIs('Resource Not Found');

        self::$server->setResponseOfPath(
            \sprintf('/api/v2/projects/%d/branches/%d/languages/progress', self::PROJECT_ID, 42),
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

        $this->subject->findByBranchId(self::PROJECT_ID, 42);
    }

    #[Test]
    public function findByDirectoryIdReturnsTranslationProgressCorrectly(): void
    {
        self::$server->setResponseOfPath(
            \sprintf('/api/v2/projects/%d/directories/%d/languages/progress', self::PROJECT_ID, 42),
            new Response(
                \file_get_contents(__DIR__ . '/Fixtures/TranslationStatus/directory-progress.json'),
                ['content-type' => 'application/json'],
                200,
            ),
        );

        $actual = $this->subject->findByDirectoryId(self::PROJECT_ID, 42);

        self::assertCount(42, $actual);
        foreach ($actual as $translationProgress) {
            self::assertInstanceOf(TranslationProgress::class, $translationProgress);
        }

        self::assertSame('ar', $actual[0]->languageId);
        self::assertSame(100, $actual[0]->translationProgress);
        self::assertSame(99, $actual[0]->approvalProgress);
        self::assertSame(79, $actual[0]->phrasesTotal);
        self::assertSame(78, $actual[0]->phrasesTranslated);
        self::assertSame(77, $actual[0]->phrasesApproved);
    }

    #[Test]
    public function findByDirectoryIdThrowsExceptionOnHttpError(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessageIs('Resource Not Found');

        self::$server->setResponseOfPath(
            \sprintf('/api/v2/projects/%d/directories/%d/languages/progress', self::PROJECT_ID, 42),
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

        $this->subject->findByDirectoryId(self::PROJECT_ID, 42);
    }
}
