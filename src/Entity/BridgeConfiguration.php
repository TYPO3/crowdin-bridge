<?php

declare(strict_types=1);

namespace App\Entity;

use App\Exception\ExtensionNotAvailableInFileConfigurationException;
use App\Exception\NoApiCredentialsException;

class BridgeConfiguration
{
    private string $configurationFile;
    protected ?array $data = null;

    public function __construct(bool $exceptionIfConfigurationFileMissing = true)
    {
        $this->configurationFile = __DIR__ . '/../../configuration.json';
        if (!is_file($this->configurationFile)) {
            if ($exceptionIfConfigurationFileMissing) {
                throw new \RuntimeException(sprintf('Configuration file %s not found', $this->configurationFile));
            }
            file_put_contents($this->configurationFile, '{}');
        }
        $this->data = json_decode((string)@file_get_contents($this->configurationFile), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return ProjectConfiguration
     * @throws NoApiCredentialsException
     */
    public function getProject(string $identifier): ProjectConfiguration
    {
        $data = $this->data['projects'][$identifier] ?? null;
        if ($data === null) {
            throw new NoApiCredentialsException(sprintf('No configuration found for "%s"', $identifier), 1566643811);
        }

        return ProjectConfiguration::initializeByArray($identifier, $data);
    }

    /**
     * @return ProjectConfiguration
     * @throws NoApiCredentialsException|ExtensionNotAvailableInFileConfigurationException
     */
    public function getProjectByExtensionKey(string $extensionKey): ProjectConfiguration
    {
        foreach ($this->data['projects'] ?? [] as $identifier => $configuration) {
            if ($configuration['extensionKey'] === $extensionKey) {
                return ProjectConfiguration::initializeByArray($identifier, $configuration);
            }
        }
        throw new ExtensionNotAvailableInFileConfigurationException(sprintf('No project found for extension key "%s"', $extensionKey));
    }

    public function getProjectByCrowdinId(int $id): ProjectConfiguration
    {
        foreach ($this->data['projects'] ?? [] as $extensionKey => $configuration) {
            if ($configuration['id'] === $id) {
                return ProjectConfiguration::initializeByArray($extensionKey, $configuration);
            }
        }
        throw new ExtensionNotAvailableInFileConfigurationException(sprintf('No project found for ID "%s"', $id));
    }

    public function add(string $project, array $data): ProjectConfiguration
    {
        $this->data['projects'][$project] = $data;
        $this->persistConfiguration();
        return ProjectConfiguration::initializeByArray($project, $data);
    }

    /**
     * @return ProjectConfiguration[]
     */
    public function getAllProjects(): array
    {
        $list = [];
        foreach ($this->data['projects'] as $identifier => $projectConfiguration) {
            $list[$identifier] = ProjectConfiguration::initializeByArray($identifier, $projectConfiguration);
        }
        return $list;
    }

    private function persistConfiguration(): void
    {
        file_put_contents($this->configurationFile, json_encode($this->data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
    }

}
