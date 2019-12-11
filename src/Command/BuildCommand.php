<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Command;

/**
 * This file is part of the "crowdin" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CrowdinBridge\Service\ExportService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BuildCommand extends BaseCommand
{

    /**
     * Defines the allowed options for this command
     *
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('crowdin:build')
            ->setDescription('Trigger build of a project')
            ->setHelp('Only if a project has been exported it is possible to get the latest translations. ')
            ->addArgument('project', InputArgument::REQUIRED, 'Project identifier')
            ->addArgument('branch', InputArgument::OPTIONAL, 'If a branch is specified, only this branch is being built', '')
            ->addArgument('async', InputArgument::OPTIONAL, 'Don\'t wait for feedback', false);
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setupConfigurationService($input->getArgument('project'));
        $branchName = $input->getArgument('branch') ?? '';
        $async = (bool)$input->getArgument('async');
        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('Project %s', $this->getProject()->getIdentifier()));

        $service = new ExportService($input->getArgument('project'));
        $service->export($branchName, $async);

        if ($branchName) {
            $io->success(sprintf('Project has been exported, limited to the branch *"%s"*!', $branchName));
        } else {
            $io->success('Project has been exported with *all branches*!');
        }
    }
}
