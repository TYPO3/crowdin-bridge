<?php

declare(strict_types=1);


namespace App\Tests\Crowdin\Dto;

use App\Crowdin\Dto\DirectoryTranslationProgress;
use App\Crowdin\Dto\TranslationProgress;
use App\Crowdin\Entity\Branch;
use App\Crowdin\Entity\Directory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DirectoryTranslationProgress::class)]
final class DirectoryTranslationProgressTest extends TestCase
{
    #[Test]
    public function translationProgressToArray(): void
    {
        $subject = new DirectoryTranslationProgress(
            new Branch(1, 42, '14.3'),
            new Directory(2, 42, 1, 100, 'adminpanel', '/14.3/typo3/sysext/adminpanel'),
            [
                new TranslationProgress('de', 60, 50, 0, 0, 0),
                new TranslationProgress('fr', 61, 51, 0, 0, 0),
                new TranslationProgress('it', 62, 52, 0, 0, 0),
            ]
        );

        $actual = $subject->translationProgressToArray();

        self::assertSame([
            'translations' => [
                'de' => 60,
                'fr' => 61,
                'it' => 62,
            ],
            'approvals' => [
                'de' => 50,
                'fr' => 51,
                'it' => 52,
            ]
        ], $actual);
    }
}
