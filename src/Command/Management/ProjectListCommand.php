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
use Symfony\Component\Console\Input\InputArgument;
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
            ->addArgument('accountKey', InputArgument::REQUIRED, 'Account key')
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
            ->addArgument('export', InputArgument::OPTIONAL, 'Export output', false)
            ->setDescription('Account listing');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Update of all accounts');

        $service = new ProjectService('');
        $response = $service->updateConfiguration(
            $input->getArgument('accountKey'),
            $input->getArgument('username')
        );

        if ($new = $response->getNewProjects()) {
            $io->success(sprintf('The following projects have been added: %s', implode(', ', $this->getIdentifiersOfProjects($new))));
        }
        if ($updated = $response->getUpdatedProjects()) {
            $io->success(sprintf('The following projects have been updated: %s', implode(', ', $this->getIdentifiersOfProjects($updated))));
        }
        if ($response->noChanges()) {
            $io->success('No changes, all fine!');
        }
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
