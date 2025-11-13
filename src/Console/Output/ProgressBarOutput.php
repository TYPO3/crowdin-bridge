<?php

declare(strict_types=1);

namespace App\Console\Output;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;

final readonly class ProgressBarOutput implements OutputInterface
{
    use OutputErrorTrait;

    public function __construct(
        private ProgressBar $progressBar,
        private SymfonyStyle $io
    ) {}

    public function start(int $max): void
    {
        $this->progressBar->start($max);
    }

    public function advance(string $text): void
    {
        $this->progressBar->advance();
    }

    public function finish(array $errors): void
    {
        $this->outputErrors($errors, $this->io);
        $this->progressBar->finish();
    }
}
