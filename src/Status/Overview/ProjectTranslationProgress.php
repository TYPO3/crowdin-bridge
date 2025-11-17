<?php

declare(strict_types=1);

namespace App\Status\Overview;

use App\Configuration\Project;
use App\Crowdin\Dto\TranslationProgress;

/**
 * DTO for holding the project information and
 * the translation progress together.
 */
final readonly class ProjectTranslationProgress
{
    /**
     * @param list<TranslationProgress> $progresses
     */
    public function __construct(
        public Project $project,
        public array $progresses,
    ) {}
}
