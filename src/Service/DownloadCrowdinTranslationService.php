<?php

declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Service;

use Akeneo\Crowdin\Api\Download;
use CrowdinApiClient\Model\DownloadFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use TYPO3\CrowdinBridge\Api\Wrapper\ProjectApi;
use TYPO3\CrowdinBridge\Api\Wrapper\TranslationApi;
use TYPO3\CrowdinBridge\Entity\ProjectConfiguration;
use TYPO3\CrowdinBridge\Exception\NoTranslationsAvailableException;
use TYPO3\CrowdinBridge\Info\CoreInformation;
use TYPO3\CrowdinBridge\Info\LanguageInformation;
use TYPO3\CrowdinBridge\Utility\FileHandling;
use ZipArchive;

class DownloadCrowdinTranslationService
{

    protected $originalLanguageKey = '';
    protected $finalLanguageKey = '';
    protected string $projectIdentifier;

    /** @var ProjectApi */
    protected ProjectApi $projectApi;

    /** @var TranslationApi */
    protected TranslationApi $translationApi;

    const REMOVE_ZIPS = true;

    public function __construct()
    {
        $this->projectApi = new ProjectApi();
        $this->translationApi = new TranslationApi();
    }

    public function downloadPackageCore(string $projectIdentifier, array $listOfLanguages = [])
    {
        $this->projectIdentifier = $projectIdentifier;
        $localProject = $this->projectApi->getConfiguration()->getProject($projectIdentifier);

        $buildId = $this->translationApi->getLastFinishedBuildId($localProject->getId());
        $download = $this->translationApi->downloadProject($localProject->getId(), $buildId);
        $zipFile = $this->downloadFromCrowdin2($download);

        $downloadTarget = $this->projectApi->getConfiguration()->getPathDownloads() . $projectIdentifier . '/';
        $this->unzip($zipFile, $downloadTarget);

        FileHandling::mkdir_deep($downloadTarget);

        $listOfLanguages = $listOfLanguages ?: $localProject->getLanguages();
        foreach ($listOfLanguages as $language) {
            $downloadLanguageTarget = $downloadTarget . $language . '/';

            $finder = new Finder();
            $finder->files()->in($downloadLanguageTarget)->notName($language . '.*')->notName(LanguageInformation::getLanguageForTypo3($language) . '.*');
            foreach ($finder as $file) {
                unlink($file->getRealPath());
            }

            $this->originalLanguageKey = $language;
            $this->finalLanguageKey = LanguageInformation::getLanguageForTypo3($language);

            $this->processDownloadDirectoryCore($downloadLanguageTarget, $language);
        }
        $this->moveAllToRsyncDestination();
        //   $this->cleanup($downloadTarget);
    }

    public function downloadPackageExtension(string $projectIdentifier, array $listOfLanguages = [])
    {
        $this->projectIdentifier = $projectIdentifier;
        $localProject = $this->projectApi->getConfiguration()->getProject($projectIdentifier);

        $buildId = $this->translationApi->getLastFinishedBuildId($localProject->getId());
        $download = $this->translationApi->downloadProject($localProject->getId(), $buildId);
        $zipFile = $this->downloadFromCrowdin2($download);

        $downloadTargetBase = $this->projectApi->getConfiguration()->getPathDownloads() . $projectIdentifier . '-base/';
        FileHandling::mkdir_deep($downloadTargetBase);
        $this->unzip($zipFile, $downloadTargetBase);

        $listOfLanguages = $listOfLanguages ?: $localProject->getLanguages();
        foreach ($listOfLanguages as $language) {
            $downloadTarget = $this->projectApi->getConfiguration()->getPathDownloads() . $projectIdentifier . '-' . $language . '/';

            $filesystem = new Filesystem();
            $filesystem->mirror($downloadTargetBase, $downloadTarget);
        }


        foreach ($listOfLanguages as $language) {
            try {
                $downloadTarget = $this->projectApi->getConfiguration()->getPathDownloads() . $projectIdentifier . '-' . $language . '/';
                $finder = new Finder();
                $finder->files()->in($downloadTarget)->notName($language . '.*')->notName(LanguageInformation::getLanguageForTypo3($language) . '.*');
                foreach ($finder as $file) {
                    unlink($file->getRealPath());
                }

                // skip empty directories
                $finder = new Finder();
                $count = $finder->files()->in($downloadTarget)->name($language . '.*')->name(LanguageInformation::getLanguageForTypo3($language) . '.*')->count();
                if ($count === 0) {
                    FileHandling::rmdir($downloadTarget);
//                    echo 'Removing' . $downloadTarget . chr(10);
                    continue;
                }

                $this->processDownloadDirectoryExtension($localProject, $downloadTarget, $language);
            } catch (\Exception $e) {
//                echo 'ERROR:' . $e->getMessage();
            }
        }
        $this->moveAllToRsyncDestination();
        $this->cleanup($downloadTarget);
    }

    protected function cleanup($downloadDir)
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

    protected function moveAllToRsyncDestination()
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

    protected function processDownloadDirectoryCore(string $directory, $language)
    {
        $branches = CoreInformation::getAllCoreBranches();
        foreach ($branches as $branch) {
            $sysExtDir = $directory . $branch . '/typo3/sysext/';
            if (!is_dir($sysExtDir)) {
                continue;
            }
            $sysExtList = FileHandling::get_dirs($sysExtDir);
            if (!is_array($sysExtList) || empty($sysExtList)) {
                throw new \RuntimeException(sprintf('No sysext founds in: %s', $sysExtDir), 1566422270);
            }

            $exportPath = $this->projectApi->getConfiguration()->getPathFinal();
            FileHandling::mkdir_deep($exportPath);
            $language = LanguageInformation::getLanguageForTypo3($language);
            foreach ($sysExtList as $extensionKey) {
                $source = $sysExtDir . $extensionKey;
                if (in_array($extensionKey, CoreInformation::getAllCoreExtensionKeys(), true)) {
                    $zipPath = $exportPath . sprintf('%s-l10n-%s.v%s.zip', $extensionKey, $language, CoreInformation::getVersionForBranchName($branch));
                } else {
                    $zipPath = $exportPath . sprintf('%s-l10n-%s.zip', $extensionKey, $language);
                }

                $result = $this->zipDir($source, $zipPath, $extensionKey);
            }
        }
    }

    protected function processDownloadDirectoryExtension(ProjectConfiguration $localProject, string $directory, $language)
    {
        $this->originalLanguageKey = $language;

        $firstDirFinder = new Finder();

        // is actually not the branch but the first sub dir
        $branch = 'master';
        $count = 0;
        foreach ($firstDirFinder->directories()->in($directory) as $branches) {
            if ($count === 0) {
                $branch = $branches->getBasename();
                $count++;
            }
        }

        $crowdinLanguageName = LanguageInformation::getLanguageForTypo3($language);
        $dir = $directory . $branch;
        if (!is_dir($dir)) {
            $dir = $directory . $language . '/' . $branch;
        }


        $extensionKey = $localProject->getExtensionkey();

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

    protected function zipDir($source, $destination, $prefix = '')
    {
        if (!empty($prefix)) {
            $prefix = trim($prefix, '/') . '/';
        }
        $zip = new ZipArchive();

        if (!$zip->open($destination, ZipArchive::CREATE)) {
            return false;
        }
        $zip->addEmptyDir($prefix);

        $source = str_replace('\\', '/', realpath($source));
        if (is_dir($source) === true) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, '/') + 1), ['.', '..'])) {
                    continue;
                }

                $file = realpath($file);
                $this->modifyFile($file);

                if (is_dir($file) === true) {
                    $zip->addEmptyDir($prefix . str_replace($source . '/', '', $file . '/'));
                } elseif (is_file($file) === true) {
                    $zip->addFromString($prefix . str_replace($source . '/', '', $file), file_get_contents($file));
                }
            }
        } elseif (is_file($source) === true) {
            $zip->addFromString($prefix . basename($source), file_get_contents($source));
        }

        return $zip->close();
    }

    protected function unzip(string $file, string $path)
    {
        $zip = new ZipArchive();
        $res = $zip->open($file);
        if ($res === true) {
            $zip->extractTo($path);
            $zip->close();
        } else {
            throw new \RuntimeException(sprintf('Could not extract zip "%s"', $file), 1566421924);
        }
    }

    /**
     * @param string $langage
     * @param string $branch
     * @throws NoTranslationsAvailableException
     */
    protected function downloadFromCrowdin2(DownloadFile $downloadFile = null): string
    {
        $path = $this->projectApi->getConfiguration()->getPathExport();
        FileHandling::mkdir_deep($path);

        $finalName = $path . $this->projectIdentifier . '.zip';

        if (!is_file($finalName)) {
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
     *
     * @param string $file
     */
    protected function modifyFile(string $file)
    {
        if (is_file($file)) {
            if ($this->finalLanguageKey !== $this->originalLanguageKey) {
                $content = file_get_contents($file);
                $content = str_replace(' target-language="' . $this->originalLanguageKey . '"', ' target-language="' . $this->finalLanguageKey . '"', $content);

                $result = file_put_contents($file, $content);
            }
        }
    }
}
