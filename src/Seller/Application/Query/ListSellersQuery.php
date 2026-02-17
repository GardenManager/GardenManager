<?php

declare(strict_types=1);

namespace GardenManager\Seller\Application\Query;

use GardenManager\Shared\Application\PaginatedQueryInterface;
use GardenManager\Shared\Application\QueryInterface;
use GardenManager\Shared\Domain\Pagination\PaginatedResult;
use Symfony\Component\Uid\Ulid;

/** @implements QueryInterface<PaginatedResult<SellerDetailView>> */
final readonly class ListSellersQuery implements PaginatedQueryInterface
{
    public const int DEFAULT_LIMIT = 1;

    public function __construct(
        private Ulid $ownerId,
        private int $page = 1,
        private int $limit = self::DEFAULT_LIMIT,
    ) {
    }

    public function getOwnerId(): Ulid
    {
        return $this->ownerId;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
