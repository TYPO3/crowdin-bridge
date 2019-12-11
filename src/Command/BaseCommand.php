<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Command;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CrowdinBridge\Configuration\Project;
use TYPO3\CrowdinBridge\Service\ConfigurationService;
use Symfony\Component\Console\Command\Command;

class BaseCommand extends Command
{
    /** @var ConfigurationService */
    protected $configurationService;

    public function setupConfigurationService(string $projectIdentifier): void
    {
        $this->configurationService = new ConfigurationService($projectIdentifier);
    }

    protected function getConfigurationService(): ConfigurationService
    {
        return $this->configurationService;
    }

    protected function getProject(): Project
    {
        return $this->configurationService->getProject();
    }
}
