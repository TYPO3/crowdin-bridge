<?php

declare(strict_types=1);

namespace App\Configuration;

use FriendsOfTYPO3\CrowdinBase\Configuration\ConfigurationReader;
use FriendsOfTYPO3\CrowdinBase\Configuration\Entity\Project as CrowdinBaseProject;

final readonly class ProjectCollectionFactory
{
    public function __construct(
        private ConfigurationReader $configurationReader,
    ) {}

    public function createProjectCollection(): ProjectCollection
    {
        return new ProjectCollection(...array_map(
            static fn(CrowdinBaseProject $project): Project => Project::fromCrowdinBaseProject($project),
            $this->configurationReader->read()
        ));
    }
}
