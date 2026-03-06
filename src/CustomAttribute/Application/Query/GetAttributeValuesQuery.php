<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Application\Query;

use GardenManager\CustomAttribute\Application\View\AttributeValueView;
use GardenManager\CustomAttribute\Domain\CustomAttributePermissions;
use GardenManager\Shared\Application\Attribute\RequiresPermission;
use GardenManager\Shared\Application\AuthorizedMessageInterface;
use GardenManager\Shared\Application\QueryInterface;
use Symfony\Component\Uid\Ulid;

/** @implements QueryInterface<list<AttributeValueView>> */
#[RequiresPermission(CustomAttributePermissions::VIEW)]
final readonly class GetAttributeValuesQuery implements QueryInterface, AuthorizedMessageInterface
{
    public function __construct(
        public string $entityType,
        public Ulid $entityId,
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
