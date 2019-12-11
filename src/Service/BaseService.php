<?php

declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Service;

use Akeneo\Crowdin\Client;
use TYPO3\CrowdinBridge\Utility\FileHandling;

class BaseService
{

    /** @var Client */
    protected $client;

    /** @var ConfigurationService */
    protected $configurationService;

    public function __construct(string $projectIdentifier)
    {
        if ($projectIdentifier) {
            $this->configurationService = new ConfigurationService($projectIdentifier);
            $project = $this->configurationService->getProject();
            $this->client = new Client($project->getIdentifier(), $project->getKey());
        } else {
            $this->client = new Client('', '');
            $this->configurationService = new ConfigurationService('');
        }
    }

    /**
     * Unzip an language zip file
     *
     * @param string $file path to zip file
     * @param string $path path to extract to
     * @throws \RuntimeException
     */
    protected function unzip(string $file, string $path)
    {
        $zip = zip_open($file);
        if (is_resource($zip)) {
            if (!is_dir($path)) {
                FileHandling::mkdir_deep($path);
            }
            while (($zipEntry = zip_read($zip)) !== false) {
                $zipEntryName = zip_entry_name($zipEntry);
                if (strpos($zipEntryName, '/') !== false) {
                    $zipEntryPathSegments = explode('/', $zipEntryName);
                    $fileName = array_pop($zipEntryPathSegments);
                    // It is a folder, because the last segment is empty, let's create it
                    if (empty($fileName)) {
                        FileHandling::mkdir_deep($path . implode('/', $zipEntryPathSegments));
                    } else {
                        $absoluteTargetPath = $path . implode('/', $zipEntryPathSegments) . '/' . $fileName;
//                        $absoluteTargetPath = GeneralUtility::getFileAbsFileName($path . implode('/', $zipEntryPathSegments) . '/' . $fileName);
                        if (trim($absoluteTargetPath) !== '') {
                            $return = FileHandling::writeFile(
                                $absoluteTargetPath,
                                zip_entry_read($zipEntry, zip_entry_filesize($zipEntry))
                            );
                            if ($return === false) {
                                throw new \RuntimeException('Could not write file ' . $zipEntryName, 1520170845);
                            }
                        } else {
                            throw new \RuntimeException('Could not write file ' . $zipEntryName, 1520170846);
                        }
                    }
                } else {
                    throw new \RuntimeException('Extension directory missing in zip file!', 1520170847);
                }
            }
        } else {
            throw new \RuntimeException('Unable to open zip file ' . $file, 1520170848);
        }
    }

    public function getProjectIdentifier(): string
    {
        return $this->client->getProjectIdentifier();
    }
}
