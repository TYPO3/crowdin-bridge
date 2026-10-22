<?php

declare(strict_types=1);


namespace App\Tests\Sorting;

use App\Crowdin\Entity\Branch;
use App\Sorting\BranchesSorter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(BranchesSorter::class)]
final class BranchesSorterTest extends TestCase
{
    #[Test]
    public function sortByNameWithRandomSortingOfBranchNames(): void
    {
        $subject = new BranchesSorter();

        $branches = [
            new Branch(101, 1, '11.5'),
            new Branch(102, 1, 'main'),
            new Branch(103, 1, '9.5'),
            new Branch(104, 1, '12.4'),
            new Branch(105, 1, '10.4'),
        ];

        $actual = $subject->sortByName($branches);

        self::assertSame('main', $actual[0]->name);
        self::assertSame('12.4', $actual[1]->name);
        self::assertSame('11.5', $actual[2]->name);
        self::assertSame('10.4', $actual[3]->name);
        self::assertSame('9.5', $actual[4]->name);
    }

    #[Test]
    public function sortByNameWithSortedBranchNames(): void
    {
        $subject = new BranchesSorter();

        $branches = [
            new Branch(101, 1, 'main'),
            new Branch(102, 1, '12.4'),
            new Branch(103, 1, '11.5'),
            new Branch(104, 1, '10.4'),
            new Branch(105, 1, '9.5'),
        ];

        $actual = $subject->sortByName($branches);

        self::assertSame('main', $actual[0]->name);
        self::assertSame('12.4', $actual[1]->name);
        self::assertSame('11.5', $actual[2]->name);
        self::assertSame('10.4', $actual[3]->name);
        self::assertSame('9.5', $actual[4]->name);
    }

    #[Test]
    public function sortByNameWithReverseSortedBranchNames(): void
    {
        $subject = new BranchesSorter();

        $branches = [
            new Branch(101, 1, '9.5'),
            new Branch(102, 1, '10.4'),
            new Branch(103, 1, '11.5'),
            new Branch(104, 1, '12.4'),
            new Branch(105, 1, 'main'),
        ];

        $actual = $subject->sortByName($branches);

        self::assertSame('main', $actual[0]->name);
        self::assertSame('12.4', $actual[1]->name);
        self::assertSame('11.5', $actual[2]->name);
        self::assertSame('10.4', $actual[3]->name);
        self::assertSame('9.5', $actual[4]->name);
    }
}
