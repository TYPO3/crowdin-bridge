<?php

declare(strict_types=1);

namespace App\Info;

/**
 * Version and system extension information for TYPO3 Core
 * @see /docs/CoreVersions.md
 */
class CoreInformation
{
    /**
     * Important: highest first
     *
     * First version in array defines the version for monorepo `main` branch. {@see self::getLatestVersion()}
     *
     * @var list<int> $versions
     */
    protected array $versions = [
        15,
        14,
        13,
    ];

    /**
     * Important: latest version will map to main automatically
     *
     * Major version to main development branch mapping.
     *
     * @var array<int, string> $branchMapping
     */
    protected array $branchMapping = [
        14 => '14.3',
        13 => '13.4',
    ];

    //
    /**
     * rte_ckeditor has no translations
     * @var list<string>
     */
    protected array $coreExtensions = [
        'adminpanel',
        'backend',
        'belog',
        'beuser',
        'core',
        'dashboard',
        'extbase',
        'extensionmanager',
        'felogin',
        'filelist',
        'filemetadata',
        'fluid',
        'fluid_styled_content',
        'form',
        'frontend',
        'impexp',
        'indexed_search',
        'info',
        'install',
        'linkvalidator',
        'lowlevel',
        'opendocs',
        'reactions',
        'recordlist',
        'recycler',
        'redirects',
        'reports',
        'scheduler',
        'seo',
        'setup',
        'styleguide',
        'sys_note',
        'theme_camino',
        'tstemplate',
        'viewpage',
        'webhooks',
        'workspaces',
    ];

    public function getLatestVersion(): int
    {
        $allVersions = $this->versions;
        return reset($allVersions) ?: throw new \DomainException('Latest Core version could not be determined!', 1792838931);
    }

    public function getVersionForBranchName(string $branch): int
    {
        if ($branch === 'main') {
            return self::getLatestVersion();
        }
        $version = array_search($branch, $this->branchMapping, true);
        if ($version === false) {
            throw new \UnexpectedValueException(sprintf('Branch "%s" not found', $branch), 1567647855);
        }
        return $version;
    }

    /**
     * @return list<string> List of Core extension keys
     */
    public function getAllCoreExtensionKeys(): array
    {
        return $this->coreExtensions;
    }

    /**
     * @return list<string> List of version numbers like "14.3", "13.4" and "main"
     */
    public function getAllCoreBranches(): array
    {
        $branches = array_values($this->branchMapping);
        $branches[] = 'main';

        return $branches;
    }
}
