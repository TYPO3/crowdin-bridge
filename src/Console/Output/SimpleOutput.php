<?php

declare(strict_types=1);

namespace App\Console\Output;

use Symfony\Component\Console\Style\SymfonyStyle;

final class SimpleOutput implements OutputInterface
{
    use OutputErrorTrait;

    public function __construct(
        private SymfonyStyle $io
    ) {}

    public function start(int $max): void {}

    public function advance(string $text): void
    {
        $this->io->writeln($text);
    }

    public function finish(array $errors): void
    {
        $this->outputErrors($errors, $this->io);
    }
}
