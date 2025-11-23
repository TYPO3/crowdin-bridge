<?php

declare(strict_types=1);

namespace App\Crowdin\Dto;

final readonly class Language
{
    public function __construct(
        public string $id,
        public string $name,
    ) {}
}
