<?php

declare(strict_types=1);

namespace App\Status\Overview;

use App\File\PathResolver;
use App\Utility\FileHandling;

readonly class FrontendWriter
{
    public function __construct(
        private PathResolver $pathResolver,
    ) {}

    public function write(): void
    {
        FileHandling::copyDirectory($this->pathResolver->getFrontendPath(), $this->pathResolver->getRsyncPath());
    }
}
