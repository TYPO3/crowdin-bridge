<?php

declare(strict_types=1);

namespace App\Download;

use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;

final readonly class ExtensionBranchNameResolver
{
    // The first branch name that is found will be returned.
    // So we have an explicit order here!
    private const array CONSIDERED_BRANCH_NAMES = [
        'main',
        'master',
        'release',
        'develop',
        'dev',
        'development',
    ];

    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function getBranchName(string $directory): string
    {
        $firstDirFinder = new Finder();

        $branchName = '';
        $allBranchNames = [];
        foreach ($firstDirFinder->directories()->in($directory)->depth(0) as $branches) {
            $allBranchNames[] = $branches->getBasename();
        }

        foreach (self::CONSIDERED_BRANCH_NAMES as $possibleName) {
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
