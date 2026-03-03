<?php

declare(strict_types=1);

namespace GardenManager\Seller\Application\Command;

use GardenManager\Seller\Domain\SellerPermissions;
use GardenManager\Shared\Application\Attribute\RequiresPermission;
use GardenManager\Shared\Application\AuthorizedMessageInterface;
use GardenManager\Shared\Application\CommandInterface;
use Symfony\Component\Uid\Ulid;

#[RequiresPermission(SellerPermissions::DELETE)]
final readonly class DeleteSellerCommand implements CommandInterface, AuthorizedMessageInterface
{
    public function __construct(
        public Ulid $sellerId,
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
