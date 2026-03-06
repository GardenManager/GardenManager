<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Application\Query;

use GardenManager\CustomAttribute\Application\View\DefinitionDetailView;
use GardenManager\CustomAttribute\Domain\CustomAttributePermissions;
use GardenManager\Shared\Application\Attribute\RequiresPermission;
use GardenManager\Shared\Application\AuthorizedMessageInterface;
use GardenManager\Shared\Application\QueryInterface;
use Symfony\Component\Uid\Ulid;

/** @implements QueryInterface<DefinitionDetailView> */
#[RequiresPermission(CustomAttributePermissions::VIEW)]
final readonly class GetDefinitionQuery implements QueryInterface, AuthorizedMessageInterface
{
    public function __construct(
        public Ulid $definitionId,
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
