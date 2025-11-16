<?php

declare(strict_types=1);

namespace App\Status\Overview;

use App\File\PathResolver;

final readonly class JsonStatusWriter
{
    public function __construct(
        private PathResolver $pathResolver,
    ) {}

    /**
     * @param array<string, mixed> $status
     */
    public function write(array $status): void
    {
        $path = $this->pathResolver->getRsyncPath() . '/status.json';
        $json = json_encode($status, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        if (@file_put_contents($path, $json) === false) {
            throw StatusProcessingException::fromJsonContentNotWritable($path);
        }
    }
}
