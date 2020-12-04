<?php

declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Entity;

use TYPO3\CrowdinBridge\Exception\ExtensionNotAvailableInFileConfigurationException;
use TYPO3\CrowdinBridge\Exception\NoApiCredentialsException;
use TYPO3\CrowdinBridge\Utility\FileHandling;

class BridgeConfiguration
{
    private string $configurationFile;
    protected $data = [];

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
     * @throws NoApiCredentialsException
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


    public function getPathDownloads(): string
    {
        return $this->getPath('downloads');
    }

    public function getPathExport(): string
    {
        return $this->getPath('export');
    }

    public function getPathRsync(): string
    {
        return $this->getPath('rsync');
    }

    public function getPathFinal(): string
    {
        return $this->getPath('final');
    }

    public function getPathExtracts(): string
    {
        return $this->getPath('extracts');
    }

    protected function getPath(string $key): string
    {
        $mainPath = getcwd() . '/export/';
        if (!is_dir($mainPath)) {
            FileHandling::mkdir_deep($subPath);
        }
        if (!is_dir($mainPath)) {
            throw new \RuntimeException(sprintf('Path "%s" does not exist', $mainPath), 1573629792);
        }
        $subPathKey = $_ENV['PATH_' . $key] ?? $key;
        $subPath = rtrim($mainPath, '/') . '/' . trim($subPathKey, '/') . '/';
        if (!is_dir($subPath)) {
            FileHandling::mkdir_deep($subPath);
        }

        return $subPath;
    }

    protected function persistConfiguration(): void
    {
        file_put_contents($this->configurationFile, json_encode($this->data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
    }

}
