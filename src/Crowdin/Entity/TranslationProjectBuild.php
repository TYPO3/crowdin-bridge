<?php

declare(strict_types=1);

namespace App\Crowdin\Entity;

use App\Crowdin\Enum\TranslationProjectBuildStatus as Status;
use CrowdinApiClient\Model\TranslationProjectBuild as CrowdinTranslationProjectBuild;

final readonly class TranslationProjectBuild
{
    public function __construct(
        public int $id,
        public Status $status,
        public string $error,
    ) {}

    public static function fromCrowdinTranslationProjectBuild(CrowdinTranslationProjectBuild $build): self
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
            $build->getId(),
            $status,
            $build->getError()['message'] ?? '',
        );
    }
}
