<?php

declare(strict_types=1);

namespace App\Crowdin\Repository;

use App\Crowdin\Dto\DownloadFile;
use App\Crowdin\Entity\TranslationProjectBuild;
use CrowdinApiClient\Crowdin;
use CrowdinApiClient\Model\DownloadFile as CrowdinDownloadFile;
use CrowdinApiClient\Model\TranslationProjectBuild as CrowdinTranslationProjectBuild;

/**
 * Connects to the TranslationApi of the Crowdin Client
 */
final readonly class TranslationRepository
{
    public function __construct(
        private Crowdin $client
    ) {}

    /**
     * Builds the given project and returns the status ('finished', 'inProgress', ..., null)
     * @link https://support.crowdin.com/developer/api/v2/#tag/Translations/operation/api.projects.translations.builds.post Documentation
     */
    public function buildProject(int $projectId): TranslationProjectBuild
    {
        $params = [
            'exportApprovedOnly' => true,
            'skipUntranslatedStrings' => true,
        ];

        try {
            $build = $this->client->translation->buildProject($projectId, $params);
            if (!$build instanceof CrowdinTranslationProjectBuild) {
                throw new RepositoryException('buildProject returned null', 0);
            }

            return TranslationProjectBuild::fromCrowdinTranslationProjectBuild($build);
        } catch (\Throwable $t) {
            throw RepositoryException::fromThrowable($t);
        }
    }

    /**
     * @return list<TranslationProjectBuild>
     * @link https://developer.crowdin.com/api/v2/#operation/api.projects.translations.builds.getMany Documentation
     */
    public function getProjectBuilds(int $projectId): array
    {
        try {
            return array_map(static function (CrowdinTranslationProjectBuild $crowdinTranslationProjectBuild): TranslationProjectBuild {
                return TranslationProjectBuild::fromCrowdinTranslationProjectBuild($crowdinTranslationProjectBuild);
            }, iterator_to_array($this->client->translation->getProjectBuilds($projectId)))
                    |> array_values(...);
        } catch (\Throwable $t) {
            throw RepositoryException::fromThrowable($t);
        }
    }

    /**
     * @link https://developer.crowdin.com/api/v2/#operation/api.projects.translations.builds.download.download Documentation
     */
    public function downloadProjectBuild(int $projectId, int $buildId): ?DownloadFile
    {
        try {
            $downloadFile = $this->client->translation->downloadProjectBuild($projectId, $buildId);
            if ($downloadFile instanceof CrowdinDownloadFile) {
                return DownloadFile::fromCrowdinDownloadFile($downloadFile);
            }
            return null;
        } catch (\Throwable $t) {
            throw RepositoryException::fromThrowable($t);
        }
    }
}
