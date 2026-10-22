<?php

declare(strict_types=1);

namespace App\Status\Core;

use App\Configuration\Project;
use App\Console\Output\OutputInterface;
use App\Crowdin\Dto\BranchTranslationProgress;
use App\Crowdin\Dto\DirectoryTranslationProgress;
use App\Crowdin\Entity\Branch;
use App\Crowdin\Entity\Directory;
use App\Crowdin\Repository\BranchRepository;
use App\Crowdin\Repository\DirectoryRepository;
use App\Crowdin\Repository\TranslationStatusRepository;
use App\Exception\UnavailableBranchException;
use App\File\PathResolver;
use App\Info\CoreInformation;

readonly class TranslationStatusExporter
{
    public function __construct(
        private BranchRepository $branchRepository,
        private DirectoryRepository $directoryRepository,
        private PathResolver $pathResolver,
        private StatusWriter $statusWriter,
        private TranslationStatusRepository $translationStatusRepository,
    ) {}

    public function exportProject(Project $project, OutputInterface $output): void
    {
        $errors = [];

        $output->advance('Retrieving list of branches ...');
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

        $translationProgressesByBranch = $this->exportBranches($branches, $output);
        $translationProgressesByDirectory = $this->getTranslationStatusForSystemExtensions($project, $branches, $output);

        $path = $this->pathResolver->getFrontendPath() . '/status_core.json';

        try {
            $this->statusWriter->write($path, $translationProgressesByBranch, $translationProgressesByDirectory);
            $output->advance('Status file successfully written.');
        } catch (\Throwable $t) {
            $errors[] = $t->getMessage();
        }

        $output->finish($errors);
    }

    /**
     * @param list<Branch> $branches
     * @return list<BranchTranslationProgress>
     */
    private function exportBranches(array $branches, OutputInterface $output): array
    {
        $output->advance('Retrieving branches ...');

        $branches = array_filter(
            $branches,
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
            $output->advance(sprintf('Retrieving translation statuses on branch "%s" ...', $branch->name));
            $translationProgressesByBranch[] = new BranchTranslationProgress(
                $branch,
                $this->translationStatusRepository->findByBranchId($branch->projectId, $branch->id)
            );
        }

        return $translationProgressesByBranch;
    }

    /**
     * @param list<Branch> $branches
     * @param OutputInterface $output
     * @return list<DirectoryTranslationProgress>
     */
    private function getTranslationStatusForSystemExtensions(Project $project, array $branches, OutputInterface $output): array
    {
        $translationProgressesByDirectory = [];

        foreach (CoreInformation::getAllCoreExtensionKeys() as $extensionKey) {
            $output->advance(\sprintf('Retrieving translation statuses for system extension "%s" ...', $extensionKey));

            // Filter directories by configured branches
            $directories = array_filter(
                $this->directoryRepository->findByFilter($project->id, $extensionKey),
                function (Directory $directory) use ($branches): bool {
                    try {
                        $this->getBranchForDirectory($directory, $branches);
                        return true;
                    } catch (UnavailableBranchException) {
                        return false;
                    }
                }
            ) |> array_values(...);

            foreach ($directories as $directory) {
                $translationProgressesByDirectory[] = new DirectoryTranslationProgress(
                    $this->getBranchForDirectory($directory, $branches),
                    $directory,
                    $this->translationStatusRepository->findByDirectoryId($project->id, $directory->id),
                );
            }
        }

        return $translationProgressesByDirectory;
    }

    /**
     * @param list<Branch> $branches
     */
    private function getBranchForDirectory(Directory $directory, array $branches): Branch
    {
        // Path is something like "/14.3/typo3/sysext/adminpanel"
        $branchName = explode('/', $directory->path)[1] ?? '';

        // Check, if the branch is available in the configured branches
        $detectedBranch = array_find($branches, static fn(Branch $branch): bool => $branch->name === $branchName);

        return $detectedBranch instanceof Branch ? $detectedBranch : throw new UnavailableBranchException(\sprintf('The branch "%s" is not configured', $branchName));
    }
}
