<?php

declare(strict_types=1);

namespace GardenManager\Plant\Application\Query;

use GardenManager\Plant\Application\View\PlantDetailView;
use GardenManager\Plant\Domain\PlantPermissions;
use GardenManager\Shared\Application\Attribute\RequiresPermission;
use GardenManager\Shared\Application\AuthorizedMessageInterface;
use GardenManager\Shared\Application\QueryInterface;
use Symfony\Component\Uid\Ulid;

/** @implements QueryInterface<PlantDetailView> */
#[RequiresPermission(PlantPermissions::VIEW)]
final readonly class GetPlantQuery implements QueryInterface, AuthorizedMessageInterface
{
    public function __construct(
        public Ulid $plantId,
        public Ulid $tenantId,
        public Ulid $actorUserId,
    ) {
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
