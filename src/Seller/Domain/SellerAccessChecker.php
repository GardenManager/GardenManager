<?php

declare(strict_types=1);

namespace GardenManager\Seller\Domain;

use GardenManager\Shared\Domain\Exception\EntityOwnershipException;
use Symfony\Component\Uid\Ulid;

final class SellerAccessChecker
{
    public function ensureOwnership(Seller $seller, Ulid $ownerId): void
    {
        if (!$seller->isOwnedBy($ownerId)) {
            throw EntityOwnershipException::fromEntityClassNameEntityIdAndUserId(
                Seller::class,
                $seller->getId(),
                $ownerId
            );
        }
    }
}
