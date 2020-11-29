<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Command\Management;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CrowdinBridge\Configuration\Project;
use TYPO3\CrowdinBridge\Service\Management\ProjectService;

class ProjectListCommand extends Command
{

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('crowdin:management:projectList')
            ->setDescription('Account listing');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Update of all accounts');

        $service = new ProjectService();
        $response = $service->updateConfiguration();

        if ($new = $response->getNewProjects()) {
            $io->success(sprintf('The following projects have been added: %s', implode(', ', $new)));
        }
        if ($updated = $response->getUpdatedProjects()) {
            $io->success(sprintf('The following projects have been updated: %s', implode(', ', $updated)));
        }
        if ($response->noChanges()) {
            $io->success('No changes, all fine!');
        }

        return 0;
    }

    /**
     * @param Project[]
     */
    protected function getIdentifiersOfProjects(array $projects): array
    {
        $identifiers = [];
        foreach ($projects as $project) {
            $identifiers[] = $project->getIdentifier();
        }

        return $identifiers;
    }
}
