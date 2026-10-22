<?php

declare(strict_types=1);

namespace App\Sorting;

use App\Crowdin\Entity\Branch;

final readonly class BranchesSorter
{
    /**
     * @param list<Branch> $branches
     * @return list<Branch>
     */
    public function sortByName(array $branches): array
    {
        usort($branches, static function (Branch $a, Branch $b): int {
            if ($a->name === 'main') {
                return -1;
            }
            if ($b->name === 'main') {
                return 1;
            }

            return version_compare($b->name, $a->name);
        });

        return $branches;
    }
}
