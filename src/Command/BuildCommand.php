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

    protected function configure()
    {
        $this
            ->setName('build')
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

        $service = new ExportService();
        $response = $service->export($projectIdentifier);
        $text = sprintf('Project "%s" has been exported!', $projectIdentifier);
        $status = 'comment';
        if ($response) {
            if ($response->getStatus() === 'finished' && $response->getProgress() === 100) {
                $status = 'info';
            }
            $text .= chr(10) . sprintf('   ... with progress "%s": %s%%.', $response->getStatus(), $response->getProgress());
        }
        if ($status === 'info') {
            $io->writeln('<info>' . $text . '</info>' . chr(10));
        } else {
            $io->writeln('<comment>' . $text . '</comment>' . chr(10));
        }

        return 0;
    }
}
