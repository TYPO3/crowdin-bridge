<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Entity;

use TYPO3\CrowdinBridge\Exception\ConfigurationException;
use TYPO3\CrowdinBridge\Exception\NoApiCredentialsException;
use TYPO3\CrowdinBridge\Utility\FileHandling;

final class ProjectConfiguration
{

    private const ENABLE_T3_EXPORT = false;

    protected int $id;

    protected string $extensionkey = '';

    protected array $languages = [];

    protected string $crowdinIdentifier;

    protected string $branch = 'master';


    /**
     * Project constructor.
     * @param string $identifier
     * @param array $configuration
     */
    public function __construct(string $crowdinIdentifier, array $configuration)
    {
        $this->crowdinIdentifier = $crowdinIdentifier;
        $this->extensionkey = $configuration['extensionKey'];
        $this->id = (int)($configuration['id'] ?? 0);
        $this->languages = FileHandling::trimExplode(',', $configuration['languages'] ?? '', true);
        $this->branch = $configuration['branch'] ?? 'master';
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getExtensionkey(): string
    {
        return $this->extensionkey;
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


    /**
     * @return bool
     * @throws NoApiCredentialsException
     */
    public function isCoreProject(): bool
    {
        die('hm');
        return $this->identifier === 'typo3-cms';
    }

    public static function initializeByArray(string $extensionKey, $data)
    {
        return new self($extensionKey, $data);
    }

    public function __toString()
    {
        return json_encode([
            'identifier' => $this->identifier,
        ]);
    }
}
