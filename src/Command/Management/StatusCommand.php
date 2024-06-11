<?php

declare(strict_types=1);

namespace App\Command\Management;

use App\Service\Management\StatusService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:management:status',
    description: 'Status of all crowdin projects',
    hidden: false
)]
class StatusCommand extends Command
{
    public function __construct(protected StatusService $statusService, ?string $name = null)
    {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Status of all projects');

        $response = $this->statusService->getStatus(true);

        $io->info('Status has been exported!');
        return 0;
    }

    private function spread(array $existing, array $add): array
    {
        foreach ($add as $value) {
            $existing[] = $value;
        }
        return $existing;
    }

}
