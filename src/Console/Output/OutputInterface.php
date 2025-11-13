<?php

declare(strict_types=1);

namespace App\Console\Output;

interface OutputInterface
{
    public function start(int $max): void;

    public function advance(string $text): void;

    /**
     * @param list<string> $errors
     */
    public function finish(array $errors): void;
}
