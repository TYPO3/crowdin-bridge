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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectIdentifier = $input->getArgument('project');

        $io = new SymfonyStyle($input, $output);

        try {
            $service = new ExportService();
            $response = $service->export($projectIdentifier);
            $text = sprintf('Project "%s" has been exported', $projectIdentifier);
            $status = 'comment';
            if ($response) {
                if ($response->getStatus() === 'finished' && $response->getProgress() === 100) {
                    $status = 'info';
                }
                $text .= sprintf(' with progress "%s": %s%%.', $response->getStatus(), $response->getProgress());
            }
            if ($status === 'info') {
                $io->info($text);
            } else {
                $io->comment($text);
            }
        } catch (\Exception $e) {
            $io->error(sprintf('ERROR with project "%s": %s', $projectIdentifier, $e->getMessage()));
        }

        return 0;
    }
}
