<?php

declare(strict_types=1);

namespace App\Command\Status;

use App\Configuration\Project;
use App\Configuration\ProjectCollection;
use App\Console\Output\SimpleOutput;
use App\Status\Core\TranslationStatusExporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:status:core',
    description: 'Retrieve core translation status and write status for translation status page',
)]
final class CoreStatusCommand extends Command
{
    public function __construct(
        private readonly ProjectCollection $projectCollection,
        private readonly TranslationStatusExporter $translationStatusExporter,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $project = $this->projectCollection->findByIdentifier('typo3-cms');
            if (!$project instanceof Project) {
                $io->error('Project with identifier "typo3-cms" not found');
                return Command::FAILURE;
            }

            $this->translationStatusExporter->exportProject($project, new SimpleOutput($io));
        } catch (\Throwable $t) {
            $io->error(sprintf('An error occurred: %s', $t->getMessage()));
            $io->write($t->getTraceAsString());

            return Command::FAILURE;
        }

        $io->success('Core status has been exported');

        return Command::SUCCESS;
    }
}
