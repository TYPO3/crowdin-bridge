<?php

declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Service\Management;

use TYPO3\CrowdinBridge\Configuration\Project;
use TYPO3\CrowdinBridge\Exception\ConfigurationException;
use TYPO3\CrowdinBridge\Exception\NoApiCredentialsException;
use TYPO3\CrowdinBridge\ExtendedApi\UpdateProject\Accounting;
use TYPO3\CrowdinBridge\Service\BaseService;
use TYPO3\CrowdinBridge\Service\StatusService as ProjectStatusService;
use TYPO3\CrowdinBridge\Utility\FileHandling;

class StatusService extends BaseService
{
    public function getStatus(string $accountKey, string $username, bool $exportConfiguration = false): array
    {
        $projectService = new ProjectService('');
        $projects = $projectService->getAllProjects($accountKey, $username);

        $collection = [];
        foreach ($projects as $project) {
//            if (!in_array($project['identifier'], ['typo3-cms', 'typo3-extension-news', 'typo3-extension-rxshariff', 'typo3-extension-ttaddress'], true)) {
//                continue;
//            }
            try {
                $service = new ProjectStatusService($project['identifier']);
                $project = $service->configurationService->getProject();

                $collection[$project->getIdentifier()] = [
                    'project' => $project,
                    'languages' => $service->get()
                ];
            } catch (NoApiCredentialsException $e) {
                // skip ext
            }
        }

        $languagesOfCore = [];
        foreach ($collection['typo3-cms']['languages'] as $language) {
            $languagesOfCore[] = $language['code'];
        }

        $output = [];
        foreach ($collection as $item) {
            /** @var Project $p */
            $p = $item['project'];
            $languages = $item['languages'];

            try {
                $extensionKey = $p->getExtensionkey();
            } catch (ConfigurationException $e) {
                $extensionKey = '';
            }
            $projectLine = [
                'extensionKey' => $extensionKey,
                'crowdinKey' => $p->getIdentifier(),
            ];

            $languageInfo = [];
            $projectUsable = false;

            foreach ($languagesOfCore as $languageOfCore) {
                $status = '-';
                foreach ($languages as $language) {
                    if ($language['code'] === $languageOfCore) {
                        $status = $language['translated_progress'];
                        if ($status !== '-' && $status > 0) {
                            $projectUsable = true;
                        }
                        continue;
                    }
                }
                $languageInfo[$languageOfCore] = $status;
            }
            $projectLine['languages'] = $languageInfo;
            $projectLine['usable'] = $projectUsable;

            $output[] = $projectLine;
        }

        if ($exportConfiguration) {
            $this->exportJson($output);
            $this->exportHtml($output);
        }
        return $output;
    }

    protected function exportJson(array $configuration): void
    {
        $filename = $this->configurationService->getPathRsync() . 'status.json';
        file_put_contents($filename, json_encode($configuration, JSON_PRETTY_PRINT));
    }

    protected function exportHtml(array $configuration): void
    {
        $pathToRoot = __DIR__ . '/../../../';
        $view = new \TYPO3Fluid\Fluid\View\TemplateView();
        $view->getTemplatePaths()->setTemplatePathAndFilename($pathToRoot . 'templates/Templates/Status.html');
        $view->assignMultiple([
            'date' => date('r'),
            'configuration' => $configuration
        ]);

        $filename = $this->configurationService->getPathRsync() . 'status.html';
        FileHandling::copyDirectory($pathToRoot . 'public/frontend/', $this->configurationService->getPathRsync());
        file_put_contents($filename, $view->render());
    }

    private function spread(array $existing, array $add): array
    {
        foreach ($add as $value) {
            $existing[] = $value;
        }
        return $existing;
    }
}
