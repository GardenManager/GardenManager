<?php

declare(strict_types=1);

namespace GardenManager\Plant\Application\Query;

use GardenManager\Plant\Application\View\PlantDetailView;
use GardenManager\Plant\Domain\PlantPermissions;
use GardenManager\Shared\Application\Attribute\RequiresPermission;
use GardenManager\Shared\Application\AuthorizedMessageInterface;
use GardenManager\Shared\Application\PaginatedQueryInterface;
use GardenManager\Shared\Domain\Pagination\PaginatedResult;
use Symfony\Component\Uid\Ulid;

/** @implements PaginatedQueryInterface<PaginatedResult<PlantDetailView>> */
#[RequiresPermission(PlantPermissions::LIST)]
final readonly class ListPlantsQuery implements PaginatedQueryInterface, AuthorizedMessageInterface
{
    public const int DEFAULT_LIMIT = 10;

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
