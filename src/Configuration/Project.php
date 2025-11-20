<?php

declare(strict_types=1);

namespace App\Configuration;

use FriendsOfTYPO3\CrowdinBase\Configuration\Entity\Language as CrowdinBaseLanguage;
use FriendsOfTYPO3\CrowdinBase\Configuration\Entity\Project as CrowdinBaseProject;

final readonly class Project
{
    /**
     * @param int $id The Crowdin ID, for example: 368353
     * @param string $identifier The Crowdin identifier, for example: "typo3-extension-news"
     * @param string $extensionKey The TYPO3 extension key, for example: "news"
     * @param list<Language> $languages
     */
    public function __construct(
        public int $id,
        public string $identifier,
        public string $extensionKey,
        public array $languages,
    ) {}

    public function isCoreProject(): bool
    {
        return $this->identifier === 'typo3-cms';
    }

    public static function fromCrowdinBaseProject(CrowdinBaseProject $project): self
    {
        return new self(
            $project->id,
            $project->identifier,
            $project->extensionKey,
            array_map(
                static fn(CrowdinBaseLanguage $language): Language => Language::fromCrowdinBaseLanguage($language),
                $project->languages
            )
        );
    }
}
