<?php

declare(strict_types=1);

namespace App\Build;

final readonly class Progress
{
    public function __construct(
        public \Closure $start,
        public \Closure $advance,
        public \Closure $finish,
    ) {}
}
