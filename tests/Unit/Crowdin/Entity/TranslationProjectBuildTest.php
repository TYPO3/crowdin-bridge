<?php

declare(strict_types=1);

namespace App\Tests\Unit\Crowdin\Entity;

use App\Crowdin\Entity\TranslationProjectBuild;
use App\Crowdin\Enum\TranslationProjectBuildStatus;
use CrowdinApiClient\Model\TranslationProjectBuild as CrowdinTranslationProjectBuild;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TranslationProjectBuild::class)]
final class TranslationProjectBuildTest extends TestCase
{
    #[Test]
    public function fromCrowdinTranslationProjectBuildStatusInstantiatesObjectCorrectly(): void
    {
        $build = new CrowdinTranslationProjectBuild([
            'id' => 42,
            'status' => 'failed',
            'error' => [
                'message' => 'some error',
            ],
        ]);

        $actual = TranslationProjectBuild::fromCrowdinTranslationProjectBuild($build);

        self::assertSame(42, $actual->id);
        self::assertSame(TranslationProjectBuildStatus::Failed, $actual->status);
        self::assertSame('some error', $actual->error);
    }

    #[Test]
    public function fromCrowdinTranslationProjectBuildStatusThrowsExceptionOnUnknownStatus(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageIs('TranslationProjectBuildStatus cannot be determined, given status: "foobar"');

        $build = new CrowdinTranslationProjectBuild([
            'status' => 'foobar',
        ]);

        TranslationProjectBuild::fromCrowdinTranslationProjectBuild($build);
    }
}
