<?php

declare(strict_types=1);

namespace App\Info;

final readonly class LanguageInformation
{
    private const array CROWDIN_TYPO3_MAPPING = [
        'es-ES' => 'es',
        'sv-SE' => 'sv',
        'fr-CA' => 'fr_CA',
        'pt-BR' => 'pt_BR',
        'zh-CN' => 'zh_CN',
        'zh-HK' => 'zh',
        'pt-PT' => 'pt',
    ];

    public static function getLanguageForTypo3(string $language): string
    {
        return self::CROWDIN_TYPO3_MAPPING[$language] ?? $language;
    }
}
