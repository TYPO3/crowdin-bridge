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
    private const VERSIONS = [13, 12, 11, 10, 9];

    /**
     * Important: latest version will map to main automatically
     */
    private const BRANCHMAPPING = [
        12 => '12.4',
        11 => '11.5',
        10 => '10.4',
        9 => '9.5'
    ];

    // rte_ckeditor got no translations
    private const CORE_EXTENSIONS = [
        'about', 'adminpanel',
        'backend', 'beuser', 'belog', 'core', 'extbase', 'extensionmanager', 'felogin', 'filelist',
        'filemetadata', 'fluid', 'frontend', 'fluid_styled_content', 'form', 'frontend', 'impexp',
        'indexed_search', 'info', 'install', 'linkvalidator', 'lowlevel', 'opendocs', 'reactions',
        'recordlist', 'recycler', 'redirects', 'reports', 'scheduler', 'seo', 'setup', 'sys_note',
         't3editor', 'tstemplate', 'viewpage', 'webhooks', 'workspaces',
    ];
    private const CORE_EXTENSIONS_9 = [
        'info', 'rsaauth', 'sys_action', 'taskcenter'
    ];

    private const CORE_EXTENSIONS_10 = [
        'dashboard'
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
        if ($branch === 'main') {
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
            return 'main';
        }
        return self::BRANCHMAPPING[$version];
    }

    public static function getCoreExtensionKeys(int $version): array
    {
        if ($version >= 10) {
            return array_merge(self::CORE_EXTENSIONS, self::CORE_EXTENSIONS_10);
        }
        return array_merge(self::CORE_EXTENSIONS, self::CORE_EXTENSIONS_9);
    }

    public static function getAllCoreExtensionKeys(): array
    {
        return array_merge(self::CORE_EXTENSIONS, self::CORE_EXTENSIONS_9, self::CORE_EXTENSIONS_10);
    }

    public static function getAllCoreBranches(): array
    {
        $branches = array_values(self::BRANCHMAPPING);
        $branches[] = 'main';

        return $branches;
    }
}
