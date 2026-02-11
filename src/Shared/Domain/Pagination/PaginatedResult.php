<?php

namespace GardenManager\Shared\Domain\Pagination;

/**
 * @template T
 */
final readonly class PaginatedResult
{
    /**
     * @param list<T> $items
     */
    public function __construct(
        public array $items,
        public int $currentPage,
        public int $perPage,
        public int $totalItems,
    ) {
    }

    public function totalPages(): int
    {
        if ($this->totalItems === 0) {
            return 1;
        }

        return (int) ceil($this->totalItems / $this->perPage);
    }

    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->totalPages();
    }

    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    public function isEmpty(): bool
    {
        return $this->totalItems === 0;
    }

    /**
     * @template U
     * @param callable(T): U $callback
     * @return self<U>
     */
    public function map(callable $callback): self
    {
        return new self(
            items: array_map($callback, $this->items),
            currentPage: $this->currentPage,
            perPage: $this->perPage,
            totalItems: $this->totalItems,
        );
    }
}
