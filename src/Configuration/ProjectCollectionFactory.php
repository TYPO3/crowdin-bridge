<?php

declare(strict_types=1);

namespace App\Configuration;

use FriendsOfTYPO3\CrowdinBase\Configuration\ConfigurationReader;

final readonly class ProjectCollectionFactory
{
    public function __construct(
        private ConfigurationReader $configurationReader,
    ) {}

    public function createProjectCollection(): ProjectCollection
    {
        return new ProjectCollection(...$this->configurationReader->read());
    }
}
