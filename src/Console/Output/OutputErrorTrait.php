<?php

declare(strict_types=1);

namespace App\Console\Output;

use Symfony\Component\Console\Style\SymfonyStyle;

trait OutputErrorTrait
{
    /**
     * @param list<string> $errors
     */
    public function outputErrors(array $errors, SymfonyStyle $io): void
    {
        foreach ($errors as $error) {
            $io->error($error);
        }
    }
}
