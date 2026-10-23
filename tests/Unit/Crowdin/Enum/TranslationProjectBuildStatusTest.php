<?php

declare(strict_types=1);


namespace App\Tests\Unit\Crowdin\Enum;

use App\Crowdin\Enum\TranslationProjectBuildStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TranslationProjectBuildStatus::class)]
final class TranslationProjectBuildStatusTest extends TestCase
{
    #[Test]
    #[DataProvider('providerForConsoleMessage')]
    public function consoleMessage(TranslationProjectBuildStatus $status, string $expected, string $error = ''): void
    {
        $actual = $status->consoleMessage('some-project', $error);

        self::assertSame($expected, $actual);
    }

    public static function providerForConsoleMessage(): iterable
    {
        yield 'canceled' => [
            'status' => TranslationProjectBuildStatus::Canceled,
            'expected' => '<info>Project "some-project" has been canceled</info>',
        ];

        yield 'created' => [
            'status' => TranslationProjectBuildStatus::Created,
            'expected' => '<info>Project "some-project" has been created</info>',
        ];

        yield 'failed' => [
            'status' => TranslationProjectBuildStatus::Failed,
            'error' => 'Some error',
            'expected' => '<error>Project "some-project" has failed (Some error)</error>',
        ];

        yield 'finished' => [
            'status' => TranslationProjectBuildStatus::Finished,
            'expected' => '<info>Project "some-project" is already built</info>',
        ];

        yield 'in progress' => [
            'status' => TranslationProjectBuildStatus::InProgress,
            'expected' => '<info>Project "some-project" has been built now</info>',
        ];
    }
}
