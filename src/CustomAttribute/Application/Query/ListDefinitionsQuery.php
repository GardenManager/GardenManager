<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Application\Query;

use GardenManager\CustomAttribute\Application\View\DefinitionDetailView;
use GardenManager\CustomAttribute\Domain\CustomAttributePermissions;
use GardenManager\Shared\Application\Attribute\RequiresPermission;
use GardenManager\Shared\Application\AuthorizedMessageInterface;
use GardenManager\Shared\Application\PaginatedQueryInterface;
use GardenManager\Shared\Domain\Pagination\PaginatedResult;
use Symfony\Component\Uid\Ulid;

/** @implements PaginatedQueryInterface<PaginatedResult<DefinitionDetailView>> */
#[RequiresPermission(CustomAttributePermissions::LIST)]
final readonly class ListDefinitionsQuery implements PaginatedQueryInterface, AuthorizedMessageInterface
{
    public const int DEFAULT_LIMIT = 20;

    public function __construct(
        public Ulid $actorUserId,
        public Ulid $tenantId,
        public ?string $entityType = null,
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
