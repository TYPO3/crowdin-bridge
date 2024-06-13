<?php

declare(strict_types=1);

namespace App\Service;

use App\Api\Wrapper\ProjectApi;
use App\Api\Wrapper\TranslationApi;
use App\Entity\ProjectConfiguration;
use App\Exception\NoTranslationsAvailableException;
use App\Info\CoreInformation;
use App\Info\LanguageInformation;
use App\Utility\FileHandling;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class DownloadCrowdinTranslationService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected string $originalLanguageKey = '';
    protected string $finalLanguageKey = '';
    protected string $projectIdentifier;
    private const bool REMOVE_ZIPS = true;

    public function __construct(
        protected readonly ProjectApi $projectApi,
        protected readonly TranslationApi $translationApi
    )
    {
    }

    public function downloadPackageCore(array $listOfLanguages = []): void
    {
        $this->logger->info('Download TYPO3 core translations');
        $projectIdentifier = 'typo3-cms';
        $this->projectIdentifier = $projectIdentifier;
        $localProject = $this->projectApi->getConfiguration()->getProject($projectIdentifier);

        // 1st: Generate base directory
        $downloadTarget = $this->download($localProject, $projectIdentifier . '/');

        $listOfLanguages = array_unique($listOfLanguages ?: $localProject->getLanguages());
        foreach ($listOfLanguages as $language) {
            $directory = $downloadTarget . $language . '/';
            $this->logger->info('Target directory: ' . $directory);

            // 2nd: Iterate over every language directory
            // and remove all files that are not for the current language
            $this->removeFilesFromDifferentLanguage($directory, $language);

            $this->originalLanguageKey = $language;
            $this->finalLanguageKey = LanguageInformation::getLanguageForTypo3($language);

            $this->processDownloadDirectoryCore($directory, $language);
        }
        $this->moveAllToRsyncDestination();
        //   $this->cleanup($downloadTarget);
    }

    public function downloadPackageExtension(string $projectIdentifier, array $listOfLanguages = []): void
    {
        $this->projectIdentifier = $projectIdentifier;
        $localProject = $this->projectApi->getConfiguration()->getProject($projectIdentifier);
        $this->logger->info(sprintf('==== Download extension "%s"', $projectIdentifier));

        // 1st: Generate base directory
        $customPath = $projectIdentifier . '-base/';
        // todo refactor with ::download
        $zipFile = $this->downloadFromCrowdin($localProject);
        $downloadTargetBase = $this->projectApi->getConfiguration()->getPathDownloads() . $customPath;
        FileHandling::rmdir($downloadTargetBase, true);
        FileHandling::mkdir_deep($downloadTargetBase);
        $this->unzip($zipFile, $downloadTargetBase);

        // 2nd: Duplicate base directory for each language
        $listOfLanguages = array_unique($listOfLanguages ?: $localProject->getLanguages());
        foreach ($listOfLanguages as $language) {
            $downloadTarget = $this->projectApi->getConfiguration()->getPathDownloads() . $projectIdentifier . '-' . $language . '/';
            $this->logger->info('Target directory: ' . $downloadTarget);
            FileHandling::rmdir($downloadTarget, true);

            $filesystem = new Filesystem();
            $filesystem->mirror($downloadTargetBase, $downloadTarget);
        }

        foreach ($listOfLanguages as $language) {
            clearstatcache(true);
            //            try {
            $downloadTarget = $this->projectApi->getConfiguration()->getPathDownloads() . $projectIdentifier . '-' . $language . '/';

            // 3rd: Iterate over every language directory
            // and remove all files that are not for the current language
            $this->removeFilesFromDifferentLanguage($downloadTarget, $language);

            // 4th: Skip empty directories
            $finder = new Finder();
            $count = $finder->files()->in($downloadTarget)->name($language . '.*')->name(LanguageInformation::getLanguageForTypo3($language) . '.*')->count();
            if ($count === 0) {
                FileHandling::rmdir($downloadTarget, true);
                continue;
            }
            $this->processDownloadDirectoryExtension($localProject, $downloadTarget, $language);
            //            } catch (\Exception $e) {
            // todo logging
            //                echo 'ERROR:' . $e->getMessage();
            //                die('TBD');
            //            }
        }
        $this->moveAllToRsyncDestination();
        //        $this->cleanup();
    }

    protected function cleanup(): void
    {
        if (self::REMOVE_ZIPS) {
            $exportDir = $this->projectApi->getConfiguration()->getPathExport();
            $exportDirs = FileHandling::get_dirs($exportDir);
            foreach ($exportDirs as $dir) {
                FileHandling::rmdir($exportDir . $dir, true);
            }
        }
        $downloadDir = FileHandling::get_dirs($this->projectApi->getConfiguration()->getPathDownloads());
        foreach ($downloadDir as $dir) {
            FileHandling::rmdir($this->projectApi->getConfiguration()->getPathDownloads() . $dir, true);
        }
    }

    protected function moveAllToRsyncDestination(): void
    {
        $exportPath = $this->projectApi->getConfiguration()->getPathFinal();
        $allPackages = FileHandling::getFilesInDir($exportPath, 'zip', true);

        foreach ($allPackages as $package) {
            $info = pathinfo($package);
            $split = explode('-', $info['basename']);
            $extensionName = $split[0];

            $projectSubDir = $this->projectApi->getConfiguration()->getPathRsync() . sprintf('%s/%s/%s-l10n/', $extensionName[0], $extensionName[1], $extensionName);
            FileHandling::mkdir_deep($projectSubDir);
            rename($package, $projectSubDir . $info['basename']);
        }
    }

    protected function processDownloadDirectoryCore(string $directory, $language): void
    {
        $branches = CoreInformation::getAllCoreBranches();
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

            $exportPath = $this->projectApi->getConfiguration()->getPathFinal();
            FileHandling::mkdir_deep($exportPath);
            $language = LanguageInformation::getLanguageForTypo3($language);
            $zipBranchName = CoreInformation::getVersionForBranchName($branch);
            foreach ($sysExtList as $extensionKey) {
                $source = $sysExtDir . $extensionKey;
                if (in_array($extensionKey, CoreInformation::getAllCoreExtensionKeys(), true)) {
                    $zipPath = $exportPath . sprintf('%s-l10n-%s.v%s.zip', $extensionKey, $language, $zipBranchName);
                } else {
                    $zipPath = $exportPath . sprintf('%s-l10n-%s.zip', $extensionKey, $language);
                }

                $this->logger->info(sprintf('Zip for "%s" in branch "%s" in "%s" ', $extensionKey, $zipBranchName, $language), ['source' => $source]);

                $result = $this->zipDir($source, $zipPath, $extensionKey);
                if (!$result) {
                    $this->logger->error(sprintf('Could not create zip for "%s" ', $extensionKey), ['source' => $source, 'zipPath' => $zipPath]);
                }
            }
        }
    }

    protected function processDownloadDirectoryExtension(ProjectConfiguration $localProject, string $directory, $language): void
    {
        $this->originalLanguageKey = $language;

        $firstDirFinder = new Finder();

        $branchName = '';
        $allowedBranchNames = ['main', 'master', 'release', 'develop', 'dev'];
        foreach ($firstDirFinder->directories()->in($directory)->depth(0) as $branches) {
            if (!$branchName && in_array($branches->getBasename(), $allowedBranchNames, true)) {
                $branchName = $branches->getBasename();
                break;
            }
        }


        if (!$branchName) {
            $all = [];
            foreach ($firstDirFinder->directories()->in($directory)->depth(0) as $branches) {
                $all[] = $branches->getBasename();
            }
            $error = sprintf('No branch found in: %s, found: %s', $directory, implode(', ', $all));
            $this->logger->error($error);
            throw new \RuntimeException($error, 1566422270);
        }
        $this->logger->info(sprintf('Used branch "%s"', $branchName));

        $crowdinLanguageName = LanguageInformation::getLanguageForTypo3($language);
        $dir = $directory . $branchName;
        if (!is_dir($dir)) {
            $dir = $directory . $language . '/' . $branchName;
        }

        $extensionKey = $localProject->getExtensionKey();

        $newDirName = $directory . $extensionKey . '/' . $crowdinLanguageName;

        if (!is_dir($newDirName)) {
            if (!is_dir($dir)) {
                throw new \UnexpectedValueException(sprintf('Directory "%s" for processing %s in %s does not exist, no translations probably available', $dir, $extensionKey, $language));
            }
            $filesystem = new Filesystem();
            $filesystem->rename($dir, $newDirName);
        }

        $exportPath = $this->projectApi->getConfiguration()->getPathFinal();

        $t3Language = $this->finalLanguageKey = LanguageInformation::getLanguageForTypo3($language);
        $zipPath = $exportPath . sprintf('%s-l10n-%s.zip', $extensionKey, $t3Language);
        $result = $this->zipDir($newDirName, $zipPath, $extensionKey);
    }

    protected function zipDir($source, $destination, $prefix = ''): bool
    {
        if (!empty($prefix)) {
            $prefix = trim($prefix, '/') . '/';
        }
        $zip = new \ZipArchive();

        if (!$zip->open($destination, \ZipArchive::CREATE)) {
            return false;
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
                    $fileHasContent = $this->modifyFile($file);
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

        return $zip->close();
    }

    protected function unzip(string $file, string $path): bool
    {
        $zip = new \ZipArchive();
        $resource = $zip->open($file);
        if ($resource === false) {
            throw new \RuntimeException(sprintf('Could not extract zip "%s"', $file), 1566421924);
        }

        $zip->extractTo($path);
        return $zip->close();
    }

    protected function downloadFromCrowdin(ProjectConfiguration $localProject): string
    {
        $path = $this->projectApi->getConfiguration()->getPathExport();
        FileHandling::mkdir_deep($path);

        $finalName = $path . $this->projectIdentifier . '.zip';

        if (!is_file($finalName)) {
            $buildId = $this->translationApi->getLastFinishedBuildId($localProject->getId());
            $downloadFile = $this->translationApi->downloadProject($localProject->getId(), $buildId);

            if (!$downloadFile) {
                throw new NoTranslationsAvailableException(sprintf('No download information provided by Crowdin API for %s', $this->projectIdentifier));
            }

            $fileContent = file_get_contents($downloadFile->getUrl());

            if (strlen($fileContent) < 130) {
                throw new NoTranslationsAvailableException(sprintf('No translations found for %s', $this->projectIdentifier));
            }
            file_put_contents($finalName, $fileContent);
        }

        return $finalName;
    }

    /**
     * Modify file's content
     * @see https://github.com/TYPO3-Initiatives/crowdin/issues/32
     */
    protected function modifyFile(string $file): bool
    {
        $content = file_get_contents($file);
        if (!str_contains($content, '<trans-unit')) {
            return false;
        }
        if ($this->finalLanguageKey !== $this->originalLanguageKey && is_file($file)) {
            $content = str_replace(' target-language="' . $this->originalLanguageKey . '"', ' target-language="' . $this->finalLanguageKey . '"', $content);

            file_put_contents($file, $content);
        }
        return true;
    }

    private function removeFilesFromDifferentLanguage(string $downloadLanguageTarget, string $language): void
    {
        $finder = new Finder();
        $finder->files()->in($downloadLanguageTarget)->notName($language . '.*')->notName(LanguageInformation::getLanguageForTypo3($language) . '.*');
        foreach ($finder as $file) {
            unlink($file->getRealPath());
        }
    }

    private function download(ProjectConfiguration $localProject, string $pathSuffix): string
    {
        $downloadTarget = $this->projectApi->getConfiguration()->getPathDownloads() . $pathSuffix;
        $zipFile = $this->downloadFromCrowdin($localProject);
        FileHandling::rmdir($downloadTarget, true);
        FileHandling::mkdir_deep($downloadTarget);
        $this->unzip($zipFile, $downloadTarget);
        return $downloadTarget;
    }
}
