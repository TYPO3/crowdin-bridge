<?php

declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Service;

use TYPO3\CrowdinBridge\Configuration\Project;
use TYPO3\CrowdinBridge\Exception\NoApiCredentialsException;
use TYPO3\CrowdinBridge\Utility\FileHandling;

class ConfigurationService
{
    private $configurationFile = '';
    protected $configuration = [];

    /** @var string */
    protected $projectIdentifier;

    public function __construct(string $project = '')
    {
        $this->configurationFile = __DIR__ . '/../../configuration.json';
        if (!is_file($this->configurationFile)) {
            throw new \RuntimeException(sprintf('Configuration file %s not found', $this->configurationFile));
        }
        $this->configuration = json_decode(file_get_contents($this->configurationFile), true);

        $this->projectIdentifier = $project;
    }

    /**
     * @return Project
     * @throws NoApiCredentialsException
     */
    public function getProject(): Project
    {
        $projectName = $this->projectIdentifier;
        if (!$projectName) {
            throw new NoApiCredentialsException('No project defined', 1566643810);
        }
        $data = $this->configuration['projects'][$projectName] ?? null;
        if ($data === null) {
            throw new NoApiCredentialsException(sprintf('No configuration found for "%s"', $projectName), 1566643811);
        }

        return Project::initializeByArray($projectName, $data);
    }

    public function add(string $project, string $key, string $extensionKey, string $languages = ''): Project
    {
        $configuration = [
            'key' => $key,
            'extensionKey' => $extensionKey
        ];
        if (!empty($languages)) {
            $configuration['languages'] = $languages;
        }
        $this->configuration['projects'][$project] = $configuration;
        $this->persistConfiguration();
        return Project::initializeByArray($project, $configuration);
    }

    public function updateSingleConfiguration(string $projectIdentifier, string $key, $value): bool
    {
        try {
            if ($value === null) {
                unset($this->configuration['projects'][$projectIdentifier][$key]);
            } else {
                $this->configuration['projects'][$projectIdentifier][$key] = $value;
            }
            $this->persistConfiguration();
            return true;
        } catch (NoApiCredentialsException $e) {
        }

        return false;
    }

    /**
     * @return string
     * @throws NoApiCredentialsException
     */
    public function getCurrentProjectName(): string
    {
        return $this->projectIdentifier;
    }

    /**
     * @return Project[]
     */
    public function getAllProjects(): array
    {
        $list = [];
        foreach ($this->configuration['projects'] as $identifier => $projectConfiguration) {
            $list[$identifier] = Project::initializeByArray($identifier, $projectConfiguration);
        }
        return $list;
    }

    /**
     * @return bool
     * @throws NoApiCredentialsException
     */
    public function isCoreProject(): bool
    {
        return $this->projectIdentifier === 'typo3-cms';
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
        $mainPath = $this->configuration['paths']['entryPath'];
        if (!is_dir($mainPath)) {
            throw new \RuntimeException(sprintf('Path "%s" does not exist', $mainPath), 1573629792);
        }
        $subPath = rtrim($mainPath, '/') . '/' . trim($this->configuration['paths'][$key], '/') . '/';
        if (!is_dir($subPath)) {
            FileHandling::mkdir_deep($subPath);
//            throw new \RuntimeException(sprintf('Path "%s" does not exist', $subPath), 1573629793);
        }

        return $subPath;
    }

    protected function persistConfiguration(): void
    {
        file_put_contents($this->configurationFile, json_encode($this->configuration, JSON_PRETTY_PRINT));
    }

}
