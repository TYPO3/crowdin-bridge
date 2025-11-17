<?php

declare(strict_types=1);

namespace App\Status\Overview;

use App\File\PathResolver;
use App\Utility\FileHandling;
use Psr\Clock\ClockInterface;

readonly class PageStatusWriter
{
    public const string DATE_FORMAT = 'D, d M Y H:i:s T';

    public function __construct(
        private ClockInterface $clock,
        private PathResolver $pathResolver,
    ) {}

    public function write(): void
    {
        $templatePath = $this->pathResolver->getTemplatesPath() . '/status.html';
        $pagePath = $this->pathResolver->getRsyncPath() . '/status.html';

        $templateContent = @file_get_contents($templatePath);
        if ($templateContent === false) {
            throw StatusProcessingException::fromMissingPageTemplateFile($templatePath);
        }

        $pageContent = str_replace(
            '{date}',
            $this->clock->now()->format(self::DATE_FORMAT),
            $templateContent
        );

        FileHandling::copyDirectory($this->pathResolver->getFrontendPath(), dirname($pagePath));
        if (@file_put_contents($pagePath, $pageContent) === false) {
            throw StatusProcessingException::fromPageContentNotWritable($pagePath);
        }
    }
}
