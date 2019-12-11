<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Info;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

/**
 * Basic information about core
 */
class CoreInformation
{

    /**
     * Important: highest first
     */
    private const VERSIONS = [10, 9, 8];

    /**
     * Important: latest version will map to master automatically
     */
    private const BRANCHMAPPING = [
        9 => '9.5',
        8 => '8.7'
    ];

    // rte_ckeditor got no translations
    private const CORE_EXTENSIONS = [
        'about', 'adminpanel',
        'backend', 'beuser', 'belog', 'core', 'extbase', 'extensionmanager', 'felogin', 'filelist',
        'filemetadata', 'fluid', 'frontend', 'fluid_styled_content', 'form', 'frontend', 'impexp',
        'indexed_search', 'info', 'install', 'linkvalidator', 'lowlevel', 'opendocs', 'recordlist',
        'recycler', 'redirects', 'reports', 'scheduler', 'seo', 'setup', 'sys_note', 't3editor',
        'tstemplate', 'viewpage', 'workspaces',
    ];
    private const CORE_EXTENSIONS_9 = [
        'info', 'rsaauth', 'sys_action', 'taskcenter'
    ];

    /**
     * @return int[]
     */
    public static function getAllVersions(): array
    {
        return self::VERSIONS;
    }

    public static function getLatestVersion(): int
    {
        $allVersions = self::VERSIONS;
        return reset($allVersions);
    }

    public static function getVersionForBranchName(string $branch): int
    {
        if ($branch === 'master') {
            return self::getLatestVersion();
        }
        $version = array_search($branch, self::BRANCHMAPPING, true);
        if ($version === null) {
            throw new \UnexpectedValueException(sprintf('Branch "%s" not found', $branch), 1567647855);
        }
        return $version;
    }

    public static function getBranchNameForVersion(int $version): string
    {
        if (!in_array($version, self::VERSIONS, true)) {
            throw new \UnexpectedValueException(sprintf('Version "%s" is not supported', $version), 1567647856);
        }
        if ($version === self::getLatestVersion()) {
            return 'master';
        }
        return self::BRANCHMAPPING[$version];
    }

    public static function getCoreExtensionKeys(int $version): array
    {
        if ($version >= 10) {
            return self::CORE_EXTENSIONS;
        }
        return array_merge(self::CORE_EXTENSIONS, self::CORE_EXTENSIONS_9);
    }

    public static function getAllCoreExtensionKeys(): array
    {
        return array_merge(self::CORE_EXTENSIONS, self::CORE_EXTENSIONS_9);
    }

    public static function getAllCoreBranches(): array
    {
        $branches = array_values(self::BRANCHMAPPING);
        $branches[] = 'master';

        return $branches;
    }
}
