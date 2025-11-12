<?php

declare(strict_types=1);

namespace App\Entity;

final class ProjectConfiguration
{
    private const bool ENABLE_T3_EXPORT = true;

    protected int $id;

    protected string $extensionKey = '';

    protected array $languages = [];

    protected string $crowdinIdentifier;

    protected string $branch = 'master';

    public function __construct(string $crowdinIdentifier, array $configuration)
    {
        $this->crowdinIdentifier = $crowdinIdentifier;
        if (!isset($configuration['extensionKey'])) {
            print_r($configuration);
            die;
        }
        $this->extensionKey = $configuration['extensionKey'];
        $this->id = (int)($configuration['id'] ?? 0);
        $this->languages = $configuration['languages'];
        $this->branch = $configuration['branch'] ?? 'master';
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getExtensionKey(): string
    {
        return $this->extensionKey;
    }

    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * @param bool $includeT3Language if set, the fake language t3 is aded
     * @return array
     */
    public function getLanguages(bool $includeT3Language = true): array
    {
        if ($includeT3Language && self::ENABLE_T3_EXPORT) {
            $this->languages[] = 't3';
        }
        return $this->languages;
    }

    public function isCoreProject(): bool
    {
        return $this->crowdinIdentifier === 'typo3-cms';
    }

    public function getCrowdinIdentifier(): string
    {
        return $this->crowdinIdentifier;
    }

    public static function initializeByArray(string $crowdinIdentifier, $configuration): ProjectConfiguration
    {
        return new self($crowdinIdentifier, $configuration);
    }

    public function __toString()
    {
        return (string)json_encode([
            'identifier' => $this->crowdinIdentifier,
        ]);
    }
}
