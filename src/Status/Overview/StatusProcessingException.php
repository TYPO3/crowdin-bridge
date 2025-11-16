<?php

declare(strict_types=1);

namespace App\Status\Overview;

final class StatusProcessingException extends \RuntimeException
{
    public static function fromMissingPageTemplateFile(string $path): self
    {
        return new self(
            \sprintf(
                'Template with path "%s" cannot be read.',
                $path,
            ),
            1763293451
        );
    }

    public static function fromPageContentNotWritable(string $path): self
    {
        return new self(
            \sprintf(
                'Page with path "%s" cannot be written.',
                $path
            ),
            1763293452
        );
    }

    public static function fromJsonContentNotWritable(string $path): self
    {
        return new self(
            \sprintf(
                'JSON with path "%s" cannot be written.',
                $path
            ),
            1763293453
        );
    }
}
