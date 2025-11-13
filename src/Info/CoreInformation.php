<?php

declare(strict_types=1);

namespace App\Info;

readonly class CoreInformation
{
    /**
     * Important: highest first
     *
     * First version in array defines the version for monorepo `main` branch. {@see self::getLatestVersion()}
     */
    private const array VERSIONS = [14, 13, 12, 11, 10, 9];

    /**
     * Important: latest version will map to main automatically
     *
     * Major version to main development branch mapping.
     */
    private const array BRANCH_MAPPING = [
        13 => '13.4',
        12 => '12.4',
        11 => '11.5',
        10 => '10.4',
        9 => '9.5',
    ];

    // rte_ckeditor got no translations
    private const array CORE_EXTENSIONS = [
        'about',
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
        'rsaauth',
        'scheduler',
        'seo',
        'setup',
        'styleguide',
        'sys_action',
        'sys_note',
        't3editor',
        'taskcenter',
        'tstemplate',
        'viewpage',
        'webhooks',
        'workspaces',
    ];

    public static function getLatestVersion(): int
    {
        $allVersions = self::VERSIONS;
        return reset($allVersions);
    }

    public static function getVersionForBranchName(string $branch): int
    {
        if ($branch === 'main') {
            return self::getLatestVersion();
        }
        $version = array_search($branch, self::BRANCH_MAPPING, true);
        if ($version === false) {
            throw new \UnexpectedValueException(sprintf('Branch "%s" not found', $branch), 1567647855);
        }
        return $version;
    }

    /**
     * @return list<string> List of Core extension keys
     */
    public static function getAllCoreExtensionKeys(): array
    {
        return self::CORE_EXTENSIONS;
    }

    /**
     * @return list<string> List of version numbers like "13.4", "12.4" and "main"
     */
    public static function getAllCoreBranches(): array
    {
        $branches = array_values(self::BRANCH_MAPPING);
        $branches[] = 'main';

        return $branches;
    }
}
