<?php

declare(strict_types=1);

namespace App\Configuration;

use FriendsOfTYPO3\CrowdinBase\Configuration\Entity\Language as CrowdinBaseLanguage;

final readonly class Language
{
    /**
     * @param non-empty-string $id The id, for example: "de"
     * @param non-empty-string $name The name, for example: "German"
     */
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
