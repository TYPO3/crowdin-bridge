<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\DownloadCrowdinTranslationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:extract:extension',
    description: 'Download translations of TYPO3 extension',
    hidden: false
)]
class ExtractExtensionCommand extends Command
{
    public function __construct(
        protected readonly DownloadCrowdinTranslationService $downloadCrowdinTranslationService,
        ?string $name = null
    ) {
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
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
            $io->title(sprintf('Extension "%s"', $projectIdentifier));
        }
        try {
            $this->downloadCrowdinTranslationService->downloadPackageExtension($projectIdentifier);

            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
                $io->success('Data has been downloaded!');
            }
        } catch (\Exception $e) {
            $io->error($e->getMessage());
        }

        return 0;
    }
}
