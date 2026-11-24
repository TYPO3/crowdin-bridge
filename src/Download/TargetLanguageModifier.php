<?php

declare(strict_types=1);

namespace App\Download;

use Psr\Log\LoggerInterface;

/**
 * Change the target-language tag of an XLIFF file from a Crowdin language key
 * (like "sv-SE") to a TYPO3 language key (like "sv"), if necessary.
 *
 * @see https://github.com/TYPO3-Initiatives/crowdin/issues/32
 * @todo Consider also XLIFF2's trgLang attribute
 */
final readonly class TargetLanguageModifier
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    public function modify(string $filePath, string $originalLanguage, string $typo3Language): bool
    {
        if ($originalLanguage === $typo3Language) {
            return true;
        }

        if (!is_file($filePath)) {
            $this->logger->warning(\sprintf('File "%s" is not a file', $filePath));
            return false;
        }

        $content = @file_get_contents($filePath);
        if ($content === false) {
            $this->logger->warning(\sprintf('Content from file "%s" cannot be read', $filePath));
            return false;
        }

        $content = str_replace(
            ' target-language="' . $originalLanguage . '"',
            ' target-language="' . $typo3Language . '"',
            $content
        );

        $result = @file_put_contents($filePath, $content);
        if ($result === false) {
            $this->logger->warning(\sprintf('Content cannot be written to file "%s"', $filePath));
            return false;
        }

        return true;
    }
}
