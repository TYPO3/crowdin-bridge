<?php

declare(strict_types=1);

namespace App\Crowdin\Repository;

use App\Crowdin\Dto\Language;
use App\File\PathResolver;

/**
 * The repository provides methods around the available Crowdin languages.
 * As languages do not change often, not the API is called directly, but
 * the languages are stored in a file and provided from that file for
 * performance reasons.
 * @see https://support.crowdin.com/developer/api/v2/#tag/Languages
 */
class LanguageRepository
{
    private const string LANGUAGES_FILE = 'languages.json';

    /**
     * @var list<Language>|null
     */
    private ?array $languages = null;

    public function __construct(
        private PathResolver $pathResolver,
    ) {}

    public function findById(string $id): ?Language
    {
        return array_first(
            array_filter(
                $this->getAllLanguages(),
                static fn(Language $language): bool => $language->id === $id
            )
        );
    }

    /**
     * @return list<Language>
     */
    private function getAllLanguages(): array
    {
        if (!is_array($this->languages)) {
            $path = $this->pathResolver->getAssetsPath() . '/' . self::LANGUAGES_FILE;
            $languagesFromFile = @file_get_contents($path);
            if ($languagesFromFile === false) {
                throw new \RuntimeException(\sprintf(
                    'The language file "%s" cannot be read!',
                    $path,
                ), 1763660710);
            }

            $this->languages = array_values(array_map(
                static fn(array $language): Language => new Language($language['id'], $language['name']),
                \json_decode($languagesFromFile, true, flags: \JSON_THROW_ON_ERROR)
            ));
        }

        return $this->languages;
    }

}
