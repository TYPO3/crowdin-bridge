<?php
declare(strict_types=1);

namespace TYPO3\CrowdinBridge\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CrowdinBridge\Service\ExportService;

class BuildCommand extends Command
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
            ->setHelp('A build is required to get later access to the translations.')
            ->addArgument('project', InputArgument::REQUIRED, 'Project identifier');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projectIdentifier = $input->getArgument('project');

        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('Project %s', $projectIdentifier));

        $service = new ExportService();
        $response = $service->export($projectIdentifier);
        $io->success('Project has been exported!');
        if ($response) {
            $io->note(sprintf('Progress "%s" with %s%%.', $response->getStatus(), $response->getProgress()));
        }

        return 0;
    }
}
