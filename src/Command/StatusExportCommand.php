<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\ExportExtensionTranslationStatusService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:status:export',
    description: 'Export extension translation status',
    hidden: false
)]
class StatusExportCommand extends Command
{
    public function __construct(
        protected readonly ExportExtensionTranslationStatusService $translationStatusService,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->addArgument('extensionKey', InputArgument::REQUIRED, 'Extension Key');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $extensionKey = $input->getArgument('extensionKey');
        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('Extension %s', $extensionKey));

        try {
            $this->translationStatusService->export($extensionKey);

        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return 1;
        }
        return 0;
    }
}
