<?php

declare(strict_types=1);


namespace App\Tests\Unit\Crowdin\Dto;

use App\Crowdin\Dto\BranchTranslationProgress;
use App\Crowdin\Dto\TranslationProgress;
use App\Crowdin\Entity\Branch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(BranchTranslationProgress::class)]
final class BranchTranslationProgressTest extends TestCase
{
    #[Test]
    public function toArray(): void
    {
        $subject = new BranchTranslationProgress(
            new Branch(101, 1, '14.3'),
            [
                new TranslationProgress('de', 70, 50, 0, 0, 0),
                new TranslationProgress('fr', 80, 60, 0, 0, 0),
                new TranslationProgress('it', 90, 70, 0, 0, 0),
            ]
        );

        $actual = $subject->toArray();

        self::assertSame([
            'branch' => '14.3',
            'translations' => [
                'de' => 70,
                'fr' => 80,
                'it' => 90,
            ],
            'approvals' => [
                'de' => 50,
                'fr' => 60,
                'it' => 70,
            ],
        ], $actual);
    }
}
