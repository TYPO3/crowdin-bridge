<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\ExportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:build',
    description: 'Trigger build of a project',
    hidden: false
)]
class BuildCommand extends Command
{
    public function __construct(protected readonly ExportService $exportService, ?string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->addArgument('project', InputArgument::REQUIRED, 'Project identifier');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectIdentifier = $input->getArgument('project');

        $io = new SymfonyStyle($input, $output);

        try {
            $response = $this->exportService->export($projectIdentifier);
            $text = sprintf('Project "%s" has been exported', $projectIdentifier);
            $status = 'comment';
            if ($response) {
                if ($response->getStatus() === 'finished' && $response->getProgress() === 100) {
                    $status = 'info';
                }
                $text .= sprintf(' with progress "%s": %s%%.', $response->getStatus(), $response->getProgress());
            }
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                if ($status === 'info') {
                    $io->info($text);
                } else {
                    $io->comment($text);
                }
            }
        } catch (\Exception $e) {
            $io->error(sprintf('ERROR with project "%s": %s', $projectIdentifier, $e->getMessage()));
        }

        return 0;
    }
}
