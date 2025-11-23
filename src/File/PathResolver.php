<?php

declare(strict_types=1);

namespace App\File;

use App\Utility\FileHandling;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Provides several application-related paths. If necessary,
 * a path is also created in the filesystem, if not existing.
 * All paths do not provide a trailing "/", so this must be added
 * in the consuming code where appropriate.
 */
readonly class PathResolver
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $projectPath
    ) {}

    public function getAssetsPath(): string
    {
        return $this->projectPath . '/assets';
    }

    public function getDownloadsPath(): string
    {
        $path = $this->projectPath . '/export/downloads';
        FileHandling::mkdir_deep($path);

        return $path;
    }

    public function getExportPath(): string
    {
        $path = $this->projectPath . '/export/export';
        FileHandling::mkdir_deep($path);

        return $path;
    }

    public function getFinalPath(): string
    {
        $path = $this->projectPath . '/export/final';
        FileHandling::mkdir_deep($path);

        return $path;
    }

    public function getFrontendPath(): string
    {
        return $this->projectPath . '/public/frontend';
    }

    public function getRsyncPath(): string
    {
        $path = $this->projectPath . '/export/rsync';
        FileHandling::mkdir_deep($path);

        return $path;
    }

    public function getTemplatesPath(): string
    {
        return $this->projectPath . '/templates';
    }
}
