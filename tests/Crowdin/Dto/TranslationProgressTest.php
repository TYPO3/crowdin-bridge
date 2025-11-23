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
        $crowdinProgress = new Progress([
            'language' => [],
            'languageId' => 'de',
            'approvalProgress' => 42,
            'phrases' => [
                'total' => 50,
                'translated' => 40,
                'approved' => 30,
            ],
        ]);

        $actual = TranslationProgress::fromCrowdinProgress($crowdinProgress);

        self::assertSame('de', $actual->languageId);
        self::assertSame(42, $actual->approvalProgress);
        self::assertSame(50, $actual->phrasesTotal);
        self::assertSame(40, $actual->phrasesTranslated);
        self::assertSame(30, $actual->phrasesApproved);
    }
}
