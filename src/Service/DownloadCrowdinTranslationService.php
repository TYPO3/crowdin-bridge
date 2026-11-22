<?php

declare(strict_types=1);

namespace App\Service;

use App\Build\ProjectNotFoundException;
use App\Configuration\Language;
use App\Configuration\Project;
use App\Configuration\ProjectCollection;
use App\Crowdin\Dto\DownloadFile;
use App\Crowdin\Entity\TranslationProjectBuild;
use App\Crowdin\Enum\TranslationProjectBuildStatus;
use App\Crowdin\Repository\TranslationRepository;
use App\Download\ExtensionBranchNameResolver;
use App\Exception\NoFinishedProjectBuildFoundException;
use App\Exception\NoTranslationsAvailableException;
use App\File\PathResolver;
use App\Info\CoreInformation;
use App\Info\LanguageInformation;
use App\Utility\FileHandling;
use App\Zip\ZipFileExtractor;
use FriendsOfTYPO3\CrowdinBase\Language\SpecialLanguage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

final readonly class DownloadCrowdinTranslationService
{
    public function __construct(
        private CoreInformation $coreInformation,
        private ExtensionBranchNameResolver $extensionBranchNameResolver,
        private LoggerInterface $logger,
        private PathResolver $pathResolver,
        private ProjectCollection $projectCollection,
        private TranslationRepository $translationRepository,
        private ZipFileExtractor $zipFileExtractor,
    ) {}

    public function downloadPackageCore(array $listOfLanguages = []): void
    {
        $this->logger->info('Download TYPO3 core translations');
        $projectIdentifier = 'typo3-cms';
        $project = $this->projectCollection->findByIdentifier($projectIdentifier);
        if (!$project instanceof Project) {
            throw ProjectNotFoundException::fromProjectIdentifier($projectIdentifier);
        }

        // 1st: Generate base directory
        $downloadTarget = $this->download($project, $projectIdentifier . '/');

        $listOfLanguages = array_unique($listOfLanguages) ?: array_map(static fn(Language $language): string => $language->id, $project->languages);
        foreach ($listOfLanguages as $language) {
            $directory = $downloadTarget . $language . '/';
            $this->logger->info('Target directory: ' . $directory);

            // 2nd: Iterate over every language directory
            // and remove all files that are not for the current language
            $this->removeFilesFromDifferentLanguage($directory, $language);

            $typo3Language = LanguageInformation::getLanguageForTypo3($language);
            $this->processDownloadDirectoryCore($directory, $language, $typo3Language);
        }
        $this->moveAllToRsyncDestination();
    }

    /**
     * @param list<string> $listOfLanguages
     */
    public function downloadPackageExtension(string $projectIdentifier, array $listOfLanguages = []): array
    {
        $exportedLanguages = [];
        $project = $this->projectCollection->findByIdentifier($projectIdentifier);
        if (!$project instanceof Project) {
            throw ProjectNotFoundException::fromProjectIdentifier($projectIdentifier);
        }

        $this->logger->info(sprintf('==== Download extension "%s"', $projectIdentifier));

        // 1st: Generate base directory
        $downloadTargetBase = $this->download($project, $projectIdentifier . '-base/');

        // 2nd: Check branch name, no need to proceed if nothing found
        $branchName = $this->extensionBranchNameResolver->getBranchName($downloadTargetBase);
        $this->logger->info(sprintf('Used branch "%s"', $branchName));

        // 3rd: Duplicate base directory for each language
        $allLanguages = [
            ...array_map(static fn(Language $language): string => $language->id, $project->languages),
            // We add the in-context language manually, as it is not delivered by Crowdin
            SpecialLanguage::IN_CONTEXT->value,
        ];
        $listOfLanguages = array_unique($listOfLanguages ?: $allLanguages);
        foreach ($listOfLanguages as $language) {
            $this->logger->info(sprintf('Language "%s"', $language));
            if (!in_array($language, $allLanguages, true)) {
                $this->logger->warning(sprintf('Language "%s" not available for extension "%s"', $language, $projectIdentifier));
                continue;
            }
            $downloadTarget = $this->pathResolver->getDownloadsPath() . '/' . $projectIdentifier . '-' . $language . '/' . $branchName . '/';
            $this->logger->info('Target directory: ' . $downloadTarget);
            FileHandling::rmdir($this->pathResolver->getDownloadsPath() . '/' . $projectIdentifier . '-' . $language . '/', true);

            $filesystem = new Filesystem();
            $filesystem->mirror($downloadTargetBase . $branchName . '/', $downloadTarget);
        }

        foreach ($listOfLanguages as $language) {
            if (!in_array($language, $allLanguages, true)) {
                continue;
            }
            clearstatcache(true);
            //            try {
            $downloadTarget = $this->pathResolver->getDownloadsPath() . '/' . $projectIdentifier . '-' . $language . '/';

            // 4th: Iterate over every language directory
            // and remove all files that are not for the current language
            $this->removeFilesFromDifferentLanguage($downloadTarget, $language);

            // 5th: Skip empty directories
            //            $finder = new Finder();
            //            $count = $finder->files()->in($downloadTarget)->name($language . '.*')->name(LanguageInformation::getLanguageForTypo3($language) . '.*')->count();
            //            if ($count === 0) {
            //                FileHandling::rmdir($downloadTarget, true);
            //                continue;
            //            }
            $exportedLanguages[$language] = $this->processDownloadDirectoryExtension($project, $downloadTarget, $branchName, $language);
            //            } catch (\Exception $e) {
            // todo logging
            //                echo 'ERROR:' . $e->getMessage();
            //                die('TBD');
            //            }
        }
        $this->moveAllToRsyncDestination();

        return $exportedLanguages;
    }

    protected function moveAllToRsyncDestination(): void
    {
        $allPackages = FileHandling::getFilesInDir($this->pathResolver->getFinalPath(), 'zip', true);

        foreach ($allPackages as $package) {
            $info = pathinfo($package);
            $split = explode('-', $info['basename']);
            $extensionName = $split[0];

            $projectSubDir = $this->pathResolver->getRsyncPath() . sprintf('/%s/%s/%s-l10n/', $extensionName[0], $extensionName[1], $extensionName);
            FileHandling::mkdir_deep($projectSubDir);
            rename($package, $projectSubDir . $info['basename']);
        }
    }

    protected function processDownloadDirectoryCore(string $directory, string $originalLanguage, string $typo3Language): void
    {
        $branches = $this->coreInformation->getAllCoreBranches();
        foreach ($branches as $branch) {
            $sysExtDir = $directory . $branch . '/typo3/sysext/';
            if (!is_dir($sysExtDir)) {
                continue;
            }
            $this->logger->info(sprintf('==== Branch "%s", working at "%s"', $branch, $sysExtDir));

            $sysExtList = FileHandling::get_dirs($sysExtDir);
            if (!is_array($sysExtList) || empty($sysExtList)) {
                $this->logger->error(sprintf('No sysext founds in: %s', $sysExtDir));
                throw new \RuntimeException(sprintf('No sysext founds in: %s', $sysExtDir), 1566422270);
            }

            $exportPath = $this->pathResolver->getFinalPath();
            FileHandling::mkdir_deep($exportPath);
            $zipBranchName = $this->coreInformation->getVersionForBranchName($branch);
            foreach ($sysExtList as $extensionKey) {
                $source = $sysExtDir . $extensionKey;
                if (in_array($extensionKey, $this->coreInformation->getAllCoreExtensionKeys(), true)) {
                    $zipPath = $exportPath . sprintf('/%s-l10n-%s.v%s.zip', $extensionKey, $typo3Language, $zipBranchName);
                } else {
                    $zipPath = $exportPath . sprintf('/%s-l10n-%s.zip', $extensionKey, $typo3Language);
                }

                $this->logger->info(sprintf('Zip for "%s" in branch "%s" in "%s" ', $extensionKey, $zipBranchName, $typo3Language), ['source' => $source]);

                $fileCount = $this->zipDir($source, $zipPath, $extensionKey, $originalLanguage, $typo3Language);
            }
        }
    }

    protected function processDownloadDirectoryExtension(Project $project, string $directory, string $branchName, string $originalLanguage): int
    {
        $typo3Language = LanguageInformation::getLanguageForTypo3($originalLanguage);
        $dir = $directory . $branchName;
        if (!is_dir($dir)) {
            $dir = $directory . $originalLanguage . '/' . $branchName;
        }

        $newDirName = $directory . $project->extensionKey . '/' . $typo3Language;

        if (!is_dir($newDirName)) {
            if (!is_dir($dir)) {
                throw new \UnexpectedValueException(sprintf('Directory "%s" for processing %s in %s does not exist, no translations probably available', $dir, $project->extensionKey, $originalLanguage));
            }
            $filesystem = new Filesystem();
            $filesystem->rename($dir, $newDirName);
        }

        $zipPath = $this->pathResolver->getFinalPath() . sprintf('/%s-l10n-%s.zip', $project->extensionKey, $typo3Language);
        return $this->zipDir($newDirName, $zipPath, $project->extensionKey, $originalLanguage, $typo3Language);
    }

    protected function zipDir(string $source, string $destination, string $prefix, string $originalLanguage, string $typo3Language): int
    {
        $prefix = trim($prefix, '/') . '/';

        $zip = new \ZipArchive();
        if (!$zip->open($destination, \ZipArchive::CREATE)) {
            throw new \RuntimeException(sprintf('Could not create zip "%s"', $destination), 1566421924);
        }
        $zip->addEmptyDir($prefix);

        $fileCount = 0;
        $source = str_replace('\\', '/', realpath($source));
        if (is_dir($source) === true) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $fileObject) {
                /** @var \SplFileInfo $fileObject */
                $file = (string)$fileObject->getRealPath();
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, '/') + 1), ['.', '..'])) {
                    continue;
                }
                $file = realpath($file);

                // skip files outside the desired target
                if (!str_starts_with($file, $source)) {
                    continue;
                }

                if (is_dir($file)) {
                    $zip->addEmptyDir($prefix . str_replace($source . '/', '', $file . '/'));
                } elseif (is_file($file)) {
                    $fileHasContent = $this->modifyFile($file, $originalLanguage, $typo3Language);
                    if (!$fileHasContent) {
                        unlink($file);
                        continue;
                    }
                    $fileCount++;
                    $zip->addFromString($prefix . str_replace($source . '/', '', $file), file_get_contents($file));
                }
            }
        } elseif (is_file($source) === true) {
            $zip->addFromString($prefix . basename($source), file_get_contents($source));
        }

        $this->logger->info(sprintf('Added %d files to zip "%s"', $fileCount, $destination));
        if (!$zip->close()) {
            throw new \RuntimeException(sprintf('Could not close zip "%s"', $destination), 1566421924);
        }

        // remove zip if no files are found
        if ($fileCount === 0) {
            unlink($destination);
        }
        return $fileCount;
    }

    protected function downloadFromCrowdin(Project $project): string
    {
        $path = $this->pathResolver->getExportPath();
        FileHandling::mkdir_deep($path);

        $finalName = $path . '/' . $project->identifier . '.zip';
        if (!is_file($finalName)) {
            $buildId = $this->getLastFinishedBuildId($project);
            $downloadFile = $this->translationRepository->downloadProjectBuild($project->id, $buildId);

            if (!$downloadFile instanceof DownloadFile) {
                throw new NoTranslationsAvailableException(
                    sprintf(
                        'No download information provided by Crowdin API for %s',
                        $project->identifier
                    )
                );
            }

            $fileContent = file_get_contents($downloadFile->url->toAsciiString()) ?: '';

            if (strlen($fileContent) < 130) {
                throw new NoTranslationsAvailableException(sprintf('No translations found for %s', $project->identifier));
            }
            file_put_contents($finalName, $fileContent);
        }
        $this->logger->info(sprintf('Downloaded "%s" to "%s"', $project->identifier, $finalName));

        return $finalName;
    }

    private function getLastFinishedBuildId(Project $project): int
    {
        $finishedBuild = array_find(
            $this->translationRepository->getProjectBuilds($project->id),
            static fn(TranslationProjectBuild $build): bool => $build->status === TranslationProjectBuildStatus::Finished
        ) ?? throw new NoFinishedProjectBuildFoundException(
            sprintf('No finished build found for project "%s"', $project->identifier)
        );

        return $finishedBuild->id;
    }

    /**
     * Modify file's content
     * @see https://github.com/TYPO3-Initiatives/crowdin/issues/32
     */
    protected function modifyFile(string $file, string $originalLanguage, string $typo3Language): bool
    {
        $content = file_get_contents($file);
        if (!str_contains($content, '<trans-unit')) {
            //            return false;
        }
        if ($typo3Language !== $originalLanguage && is_file($file)) {
            $content = str_replace(' target-language="' . $originalLanguage . '"', ' target-language="' . $typo3Language . '"', $content);

            file_put_contents($file, $content);
        }
        return true;
    }

    private function removeFilesFromDifferentLanguage(string $downloadLanguageTarget, string $language): void
    {
        $finder = new Finder();
        if (!is_dir($downloadLanguageTarget)) {
            return;
        }
        $finder->files()->in($downloadLanguageTarget)->notName($language . '.*')->notName(LanguageInformation::getLanguageForTypo3($language) . '.*');
        foreach ($finder as $file) {
            unlink($file->getRealPath());
        }
    }

    private function download(Project $project, string $pathSuffix): string
    {
        $downloadTarget = $this->pathResolver->getDownloadsPath() . '/' . $pathSuffix;
        $zipFile = $this->downloadFromCrowdin($project);
        FileHandling::rmdir($downloadTarget, true);
        FileHandling::mkdir_deep($downloadTarget);
        $this->zipFileExtractor->extract($zipFile, $downloadTarget);
        return $downloadTarget;
    }
}
