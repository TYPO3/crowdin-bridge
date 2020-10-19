<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Info;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
class LanguageInformation
{

    /**
     * Mapping Crowdin => TYPO3
     *
     * @var array
     */
    protected static $extraMapping = [
        'es-ES' => 'es',
        'sv-SE' => 'sv',
        'fr-CA' => 'fr_CA',
        'pt-BR' => 'pt_BR',
        'zh-CN' => 'ch',
        'zh-HK' => 'zh',
        'pt-PT' => 'pt',
    ];

    public static function getLanguageForTypo3(string $language): string
    {
        if (isset(self::$extraMapping[$language])) {
            return self::$extraMapping[$language];
        }
        return $language;
    }

    public static function getLanguageForCrowdin(string $language): string
    {
        $found = array_search($language, self::$extraMapping);
        if ($found === false) {
            return $language;
        }
        return $found;
    }

}
