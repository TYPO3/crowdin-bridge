<?php

declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Service\Management;

use CrowdinApiClient\Model\Progress;
use CrowdinApiClient\Model\Project as CrowdinProject;
use TYPO3\CrowdinBridge\Api\Wrapper\ProjectApi;
use TYPO3\CrowdinBridge\Configuration\Project as LocalProject;
use TYPO3\CrowdinBridge\Exception\ExtensionNotAvailableInFileConfigurationException;
use TYPO3\CrowdinBridge\ExtendedApi\UpdateProject\Accounting;
use TYPO3\CrowdinBridge\Utility\FileHandling;
use TYPO3Fluid\Fluid\View\TemplateView;

class StatusService
{
    /** @var ProjectApi */
    protected ProjectApi $projectApi;

    public function __construct()
    {
        $this->projectApi = new ProjectApi();
    }

    public function getStatus(bool $exportConfiguration = false): array
    {
        $projects = $this->projectApi->getAll();

        $collection = [];
        foreach ($projects as $project) {
            $tmp = [
                'crowdinProject' => $project,
                'localProject' => null,
                'translationStatus' => $this->projectApi->getTranslationStatusByCrowdinId($project->getId())
            ];
            try {
                $tmp['localProject'] = $this->projectApi->getConfiguration()->getProjectByCrowdinId($project->getId());
            } catch (ExtensionNotAvailableInFileConfigurationException $e) {
                // do nothing
            }
            $collection[$project->getIdentifier()] = $tmp;
        }

        $output = [];

        $languagesOfCore = [];
        foreach ($collection['typo3-cms']['crowdinProject']->getTargetLanguages() as $language) {
            $languagesOfCore[] = $language['id'];
            $output['languages'][$language['id']] = $language['name'];
        }

        asort($output['languages']);
        sort($languagesOfCore);

        foreach ($collection as $item) {
            /** @var LocalProject $localProject */
            $localProject = $item['localProject'];
            /** @var CrowdinProject $crowdinProject */
            $crowdinProject = $item['crowdinProject'];

            $projectLine = [
                'extensionKey' => $localProject ? $localProject->getExtensionkey() : '',
                'crowdinKey' => $crowdinProject->getIdentifier()
            ];

            $languageInfo = [];
            $projectUsable = false;

            foreach ($languagesOfCore as $languageOfCore) {
                $status = '-';
                foreach ($item['translationStatus'] as $language) {
                    /** @var Progress $language */
                    if ($language->getLanguageId() === $languageOfCore) {
                        $status = $language->getTranslationProgress();
                        if ($status > 0) {
                            $projectUsable = true;
                        }
                        continue;
                    }
                }
                $languageInfo[$languageOfCore] = $status;
            }
            $projectLine['languages'] = $languageInfo;
            $projectLine['usable'] = $projectUsable;

            $output['projects'][] = $projectLine;
        }

        if ($exportConfiguration) {
            $this->exportJson($output);
            $this->exportHtml($output);
        }
        return $output;
    }

    protected function exportJson(array $configuration): void
    {
        $filename = $this->projectApi->getConfiguration()->getPathRsync() . 'status.json';
        file_put_contents($filename, json_encode($configuration, JSON_PRETTY_PRINT));
    }

    protected function exportHtml(array $data): void
    {
        $pathToRoot = __DIR__ . '/../../../';
        $view = new TemplateView();
        $view->getTemplatePaths()->setTemplatePathAndFilename($pathToRoot . 'templates/Templates/Status.html');
        $view->assignMultiple([
            'date' => date('r'),
        ]);

        $coreProject = null;
        $usableProjects = $notUsableProjects = [];
        foreach ($data['projects'] as $p) {
            if ($p['crowdinKey'] === 'typo3-cms') {
                $coreProject = $p;
            } else {
                if ($p['usable']) {
                    $usableProjects[] = $p;
                } else {
                    $notUsableProjects[] = $p;
                }
            }
        }
        $view->assignMultiple([
            'coreProject' => $coreProject,
            'usableProjects' => $usableProjects,
            'countUsableProjects' => count($usableProjects) + 1, // add core
            'notUsableProjects' => $notUsableProjects
        ]);

        $filename = $this->projectApi->getConfiguration()->getPathRsync() . 'status.html';
        FileHandling::copyDirectory($pathToRoot . 'public/frontend/', $this->projectApi->getConfiguration()->getPathRsync());
        file_put_contents($filename, $view->render());
    }
}
