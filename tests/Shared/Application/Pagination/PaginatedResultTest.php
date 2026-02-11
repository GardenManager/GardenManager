<?php

namespace GardenManager\Tests\Shared\Application\Pagination;

use GardenManager\Shared\Domain\Pagination\PaginatedResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PaginatedResultTest extends TestCase
{
    #[Test]
    public function totalPagesWithExactDivision(): void
    {
        $result = new PaginatedResult(['a', 'b'], 1, 2, 6);

        self::assertSame(3, $result->totalPages());
    }

    #[Test]
    public function totalPagesRoundsUp(): void
    {
        $result = new PaginatedResult(['a', 'b'], 1, 2, 5);

        self::assertSame(3, $result->totalPages());
    }

    #[Test]
    public function totalPagesWithZeroItems(): void
    {
        $result = new PaginatedResult([], 1, 10, 0);

        self::assertSame(1, $result->totalPages());
    }

    #[Test]
    public function hasNextPageOnFirstPage(): void
    {
        $result = new PaginatedResult(['a'], 1, 1, 3);

        self::assertTrue($result->hasNextPage());
    }

    #[Test]
    public function hasNextPageOnLastPage(): void
    {
        $result = new PaginatedResult(['c'], 3, 1, 3);

        self::assertFalse($result->hasNextPage());
    }

    #[Test]
    public function hasPreviousPageOnFirstPage(): void
    {
        $result = new PaginatedResult(['a'], 1, 1, 3);

        self::assertFalse($result->hasPreviousPage());
    }

    #[Test]
    public function hasPreviousPageOnSecondPage(): void
    {
        $result = new PaginatedResult(['b'], 2, 1, 3);

        self::assertTrue($result->hasPreviousPage());
    }

    #[Test]
    public function isEmptyWithNoItems(): void
    {
        $result = new PaginatedResult([], 1, 10, 0);

        self::assertTrue($result->isEmpty());
    }

    #[Test]
    public function isEmptyWithItems(): void
    {
        $result = new PaginatedResult(['a'], 1, 10, 1);

        self::assertFalse($result->isEmpty());
    }
}
