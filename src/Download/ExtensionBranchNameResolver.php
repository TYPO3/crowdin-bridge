<?php

declare(strict_types=1);

namespace App\Download;

use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;

final readonly class ExtensionBranchNameResolver
{
    /**
     * @param list<string> $allowedBranchNames
     */
    public function __construct(
        private LoggerInterface $logger,
        private array $allowedBranchNames,
    ) {}

    public function getBranchName(string $directory): string
    {
        $firstDirFinder = new Finder();

        $branchName = '';
        $allBranchNames = [];
        foreach ($firstDirFinder->directories()->in($directory)->depth(0) as $branches) {
            $allBranchNames[] = $branches->getBasename();
        }

        foreach ($this->allowedBranchNames as $possibleName) {
            if (in_array($possibleName, $allBranchNames, true)) {
                $branchName = $possibleName;
                break;
            }
        }

        if ($branchName === '') {
            sort($allBranchNames);
            $error = sprintf('Branch not found in directory "%s", found: %s', $directory, implode(', ', $allBranchNames));
            $this->logger->error($error);
            throw new \RuntimeException($error, 1566422270);
        }

        return $branchName;
    }
}
