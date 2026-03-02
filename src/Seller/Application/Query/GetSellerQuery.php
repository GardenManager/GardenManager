<?php

declare(strict_types=1);

namespace GardenManager\Seller\Application\Query;

use GardenManager\Seller\Application\View\SellerDetailView;
use GardenManager\Seller\Domain\SellerPermissions;
use GardenManager\Shared\Application\Attribute\RequiresPermission;
use GardenManager\Shared\Application\AuthorizedMessageInterface;
use GardenManager\Shared\Application\QueryInterface;
use Symfony\Component\Uid\Ulid;

/** @implements QueryInterface<SellerDetailView> */
#[RequiresPermission(SellerPermissions::VIEW)]
final readonly class GetSellerQuery implements QueryInterface, AuthorizedMessageInterface
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
