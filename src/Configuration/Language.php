<?php

declare(strict_types=1);

namespace App\Configuration;

use FriendsOfTYPO3\CrowdinBase\Configuration\Entity\Language as CrowdinBaseLanguage;

final readonly class Language
{
    public function __construct(
        public string $id,
        public string $name,
    ) {}

    public static function fromCrowdinBaseLanguage(CrowdinBaseLanguage $language): self
    {
        return new Language(
            $language->id,
            $language->name,
        );
    }
}
