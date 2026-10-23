<?php

declare(strict_types=1);

namespace App\Crowdin\Dto;

use App\Crowdin\Enum\TranslationProjectBuildStatus as Status;
use CrowdinApiClient\Model\TranslationProjectBuild as CrowdinTranslationProjectBuild;

final readonly class TranslationProjectBuild
{
    public function __construct(
        public Status $status,
        public string $error,
    ) {}

    public static function fromCrowdinTranslationProjectBuildStatus(CrowdinTranslationProjectBuild $build): self
    {
        $status = Status::tryFrom($build->getStatus());
        if (!$status instanceof Status) {
            throw new \UnexpectedValueException(
                \sprintf(
                    'TranslationProjectBuildStatus cannot be determined, given status: "%s"',
                    $build->getStatus(),
                )
            );
        }

        return new self(
            $status,
            $build->getError()['message'] ?? '',
        );
    }
}
