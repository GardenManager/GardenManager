<?php

namespace GardenManager\Shared\Infrastructure\Http;

use GardenManager\Shared\Domain\Pagination\PaginatedResult;

/**
 * @template T
 */
final readonly class PaginatedApiResponse implements \JsonSerializable
{
    /**
     * @param list<T> $data
     * @param array{page: int, limit: int, total: int, pages: int} $pagination
     */
    public function __construct(
        public array $data,
        public array $pagination,
    ) {
    }

    /**
     * @template U
     * @param PaginatedResult<mixed> $pager
     * @param list<U> $items
     * @return self<U>
     */
    public static function fromPaginatedResult(PaginatedResult $pager, array $items): self
    {
        return new self(
            data: $items,
            pagination: [
                'page' => $pager->currentPage,
                'limit' => $pager->perPage,
                'total' => $pager->totalItems,
                'pages' => $pager->totalPages(),
            ],
        );
    }

    /** @return array{data: list<T>, pagination: array{page: int, limit: int, total: int, pages: int}} */
    public function jsonSerialize(): array
    {
        return [
            'data' => $this->data,
            'pagination' => $this->pagination,
        ];
    }
}
