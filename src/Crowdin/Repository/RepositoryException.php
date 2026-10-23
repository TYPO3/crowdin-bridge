<?php

declare(strict_types=1);

namespace App\Crowdin\Repository;

final class RepositoryException extends \RuntimeException
{
    public static function fromThrowable(\Throwable $t): self
    {
        return new self($t->getMessage(), $t->getCode(), $t);
    }
}
