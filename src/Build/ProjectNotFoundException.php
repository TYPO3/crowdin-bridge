<?php

declare(strict_types=1);

namespace App\Build;

final class ProjectNotFoundException extends \RuntimeException
{
    public static function fromProjectIdentifier(string $projectIdentifier): self
    {
        return new self(
            sprintf(
                'Project "%s" does not exist. Remember: The project name has to start with "%s"',
                $projectIdentifier,
                'typo3-extension-',
            ),
            1760703485
        );
    }
}
