<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Configuration;

use TYPO3\CrowdinBridge\Exception\ConfigurationException;
use TYPO3\CrowdinBridge\Utility\FileHandling;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
final class Project
{

    /** @var string */
    protected $identifier = '';

    /** @var string */
    protected $key = '';

    /** @var string */
    protected $extensionkey = '';

    /** @var array */
    protected $languages = [];

    /**
     * Project constructor.
     * @param string $identifier
     * @param array $configuration
     */
    public function __construct(string $identifier, array $configuration)
    {
        $this->identifier = $identifier;
        $this->key = $configuration['key'];
        $this->extensionkey = $configuration['extensionKey'] ?? '';
        $this->languages = FileHandling::trimExplode(',', $configuration['languages'] ?? '', true);
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getExtensionkey(): string
    {
        if (empty($this->extensionkey)) {
            throw new ConfigurationException('No extension key defined', 1574599928);
        }
        return $this->extensionkey;
    }

    public function getBranch()
    {
        // todo configuration

        return 'master';
    }

    /**
     * @param bool $includeT3Language if set, the fake language t3 is aded
     * @return array
     */
    public function getLanguages(bool $includeT3Language = true): array
    {
        if ($includeT3Language) {
            $this->languages[] = 't3';
        }
        return $this->languages;
    }

    public static function initializeByArray(string $identifier, $data)
    {
        return new self($identifier, $data);
    }

    public function __toString()
    {
        return json_encode([
            'identifier' => $this->identifier,
            'password' => $this->key
        ]);
    }
}
