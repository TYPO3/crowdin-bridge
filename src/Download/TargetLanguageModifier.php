<?php

declare(strict_types=1);

namespace App\Download;

use Dom\Attr;
use Dom\XMLDocument;
use Psr\Log\LoggerInterface;

/**
 * Change the target-language tag of an XLIFF file from a Crowdin language key
 * (like "sv-SE") to a TYPO3 language key (like "sv"), if necessary.
 *
 * @see https://github.com/TYPO3-Initiatives/crowdin/issues/32
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
            $this->logger->warning(sprintf('File "%s" is not a file', $filePath));
            return false;
        }

        try {
            $xliff = @XMLDocument::createFromFile($filePath);
        } catch (\Exception $e) {
            $this->logger->warning($e->getMessage());
            return false;
        }

        $version = $xliff->querySelector('xliff')?->getAttribute('version');
        if (!is_string($version)) {
            $this->logger->warning(sprintf('File "%s" has no XLIFF version attribute', $filePath));
            return false;
        }

        [$mainVersion] = explode('.', $version);
        try {
            match ((int)$mainVersion) {
                1 => $this->handleVersion1($xliff, $typo3Language),
                2 => $this->handleVersion2($xliff, $typo3Language),
                default => throw new \DomainException(sprintf('XLIFF version "%s" is not supported', $version)),
            };
        } catch (\Throwable $t) {
            $this->logger->warning(sprintf('File "%s" has errors processing XLIFF: %s', $filePath, $t->getMessage()));
            return false;
        }

        $result = @$xliff->saveXmlFile($filePath);
        if ($result === false) {
            $this->logger->warning(\sprintf('XLIFF document cannot be written to file "%s"', $filePath));
            return false;
        }

        return true;
    }

    private function handleVersion1(XMLDocument $xliff, string $typo3Language): void
    {
        $targetLanguageAttribute = $xliff->querySelector('file')?->getAttributeNode('target-language');
        if (!$targetLanguageAttribute instanceof Attr) {
            throw new \DomainException('target-language on file node not set');
        }

        $targetLanguageAttribute->value = $typo3Language;
    }

    private function handleVersion2(XMLDocument $xliff, string $typo3Language): void
    {
        $targetLanguageAttribute = $xliff->querySelector('xliff')?->getAttributeNode('trgLang');
        if (!$targetLanguageAttribute instanceof Attr) {
            throw new \DomainException('trgLang on xliff node not set');
        }

        $targetLanguageAttribute->value = $typo3Language;
    }
}
