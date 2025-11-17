<?php

declare(strict_types=1);


namespace App\Tests\Configuration;

use App\Configuration\Language;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use FriendsOfTYPO3\CrowdinBase\Configuration\Entity\Language as CrowdinBaseLanguage;

#[CoversClass(Language::class)]
final class LanguageTest extends TestCase
{
    #[Test]
    public function fromCrowdinBaseLanguage(): void
    {
        $crowdinBaseLanguage = new CrowdinBaseLanguage('de', 'German');

        $actual = Language::fromCrowdinBaseLanguage($crowdinBaseLanguage);

        self::assertSame('de', $actual->id);
        self::assertSame('German', $actual->name);
    }
}
