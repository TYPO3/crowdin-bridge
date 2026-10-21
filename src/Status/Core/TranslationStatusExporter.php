<?php

declare(strict_types=1);

namespace App\Status\Core;

use App\Configuration\Project;
use App\Console\Output\OutputInterface;
use App\Crowdin\Dto\BranchTranslationProgress;
use App\Crowdin\Entity\Branch;
use App\Crowdin\Repository\BranchRepository;
use App\Crowdin\Repository\TranslationStatusRepository;
use App\File\PathResolver;
use App\Info\CoreInformation;

readonly class TranslationStatusExporter
{
    public function __construct(
        private BranchRepository $branchRepository,
        private PathResolver $pathResolver,
        private StatusWriter $statusWriter,
        private TranslationStatusRepository $translationStatusRepository,
    ) {}

    public function exportProject(Project $project, OutputInterface $output): void
    {
        $output->advance('Retrieving branches ...');

        $branches = array_filter(
            $this->branchRepository->list($project->id),
            static function (Branch $branch): bool {
                try {
                    CoreInformation::getVersionForBranchName($branch->name);
                    return true;
                } catch (\UnexpectedValueException) {
                    return false;
                }
            }
        ) |> array_values(...);

        $output->advance(sprintf('Retrieved %d branches.', count($branches)));

        $translationProgressesByBranch = [];
        foreach ($branches as $branch) {
            $output->advance(sprintf('Retrieving translation statuses for branch "%s"', $branch->name));
            $translationProgressesByBranch[] = new BranchTranslationProgress(
                $branch,
                $this->translationStatusRepository->findByBranchId($branch->projectId, $branch->id)
            );
        }

        $path = $this->pathResolver->getFrontendPath() . '/status_core.json';

        $this->statusWriter->write($path, $translationProgressesByBranch);
    }
}
