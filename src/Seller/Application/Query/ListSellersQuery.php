<?php

declare(strict_types=1);

namespace GardenManager\Seller\Application\Query;

use GardenManager\Seller\Application\View\SellerDetailView;
use GardenManager\Seller\Domain\SellerPermissions;
use GardenManager\Shared\Application\Attribute\RequiresPermission;
use GardenManager\Shared\Application\AuthorizedMessageInterface;
use GardenManager\Shared\Application\PaginatedQueryInterface;
use GardenManager\Shared\Application\QueryInterface;
use GardenManager\Shared\Domain\Pagination\PaginatedResult;
use Symfony\Component\Uid\Ulid;

/** @implements QueryInterface<PaginatedResult<SellerDetailView>> */
#[RequiresPermission(SellerPermissions::LIST)]
final readonly class ListSellersQuery implements PaginatedQueryInterface, AuthorizedMessageInterface
{
    public const int DEFAULT_LIMIT = 1;

    public function __construct(
        public Ulid $actorUserId,
        public Ulid $tenantId,
        private int $page = 1,
        private int $limit = self::DEFAULT_LIMIT,
    ) {
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getActorUserId(): Ulid
    {
        return $this->actorUserId;
    }

    public function getTenantId(): Ulid
    {
        return $this->tenantId;
    }
}
