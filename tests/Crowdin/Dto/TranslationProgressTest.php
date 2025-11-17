<?php

declare(strict_types=1);


namespace App\Tests\Crowdin\Dto;

use App\Crowdin\Dto\TranslationProgress;
use CrowdinApiClient\Model\Progress;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TranslationProgress::class)]
final class TranslationProgressTest extends TestCase
{
    #[Test]
    public function fromCrowdinProgress(): void
    {
        $crowdinProgress = $this->createStub(Progress::class);
        $crowdinProgress
            ->method('getLanguageId')
            ->willReturn('de');
        $crowdinProgress
            ->method('getApprovalProgress')
            ->willReturn(42);

        $actual = TranslationProgress::fromCrowdinProgress($crowdinProgress);

        self::assertSame('de', $actual->languageId);
        self::assertSame(42, $actual->approvalProgress);
    }
}
