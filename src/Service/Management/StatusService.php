<?php

declare(strict_types=1);

namespace App\Service\Management;

use App\Api\Wrapper\ProjectApi;
use App\Configuration\ProjectCollection;
use App\Entity\ProjectConfiguration;
use App\Exception\ExtensionNotAvailableInFileConfigurationException;
use App\File\PathResolver;
use App\Utility\FileHandling;
use CrowdinApiClient\Model\Progress;
use FriendsOfTYPO3\CrowdinBase\Configuration\Entity\Project;
use TYPO3Fluid\Fluid\View\TemplateView;

final readonly class StatusService
{
    public function __construct(
        private PathResolver $pathResolver,
        private ProjectCollection $projectCollection,
        private ProjectApi $projectApi
    ) {}

    public function getStatus(): array
    {
        $collection = [];
        foreach ($this->projectCollection as $project) {
            $tmp = [
                'crowdinProject' => $project,
                'localProject' => null,
                'translationStatus' => $this->projectApi->getTranslationStatusByCrowdinId($project->id),
            ];
            try {
                $tmp['localProject'] = $this->projectApi->getConfiguration()->getProjectByCrowdinId($project->id);
            } catch (ExtensionNotAvailableInFileConfigurationException) {
                // do nothing
            }
            $collection[$project->identifier] = $tmp;
        }

        $output = [];

        $languagesOfCore = [];
        $output['languages'] = [];
        foreach ($collection['typo3-cms']['crowdinProject']->languages as $language) {
            $languagesOfCore[] = $language->id;
            $output['languages'][$language->id] = $language->name;
        }

        asort($output['languages']);
        sort($languagesOfCore);

        foreach ($collection as $item) {
            /** @var ProjectConfiguration $localProject */
            $localProject = $item['localProject'];
            /** @var Project $crowdinProject */
            $crowdinProject = $item['crowdinProject'];

            $projectLine = [
                'extensionKey' => $localProject ? $localProject->getExtensionkey() : '',
                'crowdinKey' => $crowdinProject->identifier,
            ];

            $languageInfo = [];
            $projectUsable = false;

            foreach ($languagesOfCore as $languageOfCore) {
                $status = '-';
                foreach ($item['translationStatus'] as $language) {
                    /** @var Progress $language */
                    if ($language->getLanguageId() === $languageOfCore) {
                        $status = $language->getApprovalProgress();
                        if ($status > 0) {
                            $projectUsable = true;
                        }
                    }
                }
                $languageInfo[$languageOfCore] = $status;
            }
            $projectLine['languages'] = $languageInfo;
            $projectLine['usable'] = $projectUsable;

            $output['projects'][] = $projectLine;
        }

        $this->exportJson($output);
        $this->exportHtml();

        return $output;
    }

    private function exportJson(array $configuration): void
    {
        $filename = $this->pathResolver->getRsyncPath() . '/status.json';
        file_put_contents($filename, json_encode($configuration, JSON_PRETTY_PRINT));
    }

    private function exportHtml(): void
    {
        $view = new TemplateView();
        $view->getRenderingContext()->getTemplatePaths()->setTemplatePathAndFilename($this->pathResolver->getTemplatesPath() . '/Status.html');
        $view->assignMultiple([
            'date' => (new \DateTime('now', new \DateTimeZone('UTC')))->format('D, d M Y H:i:s') . ' UTC',
        ]);
        $filename = $this->pathResolver->getRsyncPath() . '/status.html';
        FileHandling::copyDirectory($this->pathResolver->getFrontendPath(), $this->pathResolver->getRsyncPath());
        file_put_contents($filename, $view->render());
    }
}
