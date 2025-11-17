<?php

declare(strict_types=1);

namespace App\Console\Output;

use Symfony\Component\Console\Style\SymfonyStyle;

final class ProjectIdentifierOutput implements OutputInterface
{
    use OutputErrorTrait;

    private int $max = 0;

    public function __construct(
        private SymfonyStyle $io
    ) {}

    public function start(int $max): void
    {
        $this->max = $max;
        $this->io->writeln(\sprintf('Processing %d projects ...', $this->max));
    }

    public function advance(string $text): void
    {
        static $count = 0;
        $maxLength = strlen((string)$this->max);

        $this->io->writeln(\sprintf("% {$maxLength}d/%d: %s", ++$count, $this->max, $text));
    }

    public function finish(array $errors): void
    {
        $this->outputErrors($errors, $this->io);
    }
}
