<?php

namespace GardenManager\Plant\Domain\Security;

use GardenManager\Plant\Domain\Entity\Plant;
use GardenManager\Shared\Domain\Exception\EntityOwnershipException;
use Symfony\Component\Uid\Ulid;

class PlantAccessChecker
{
    public function ensureOwnership(Plant $plant, Ulid $ownerId): void
    {
        if (!$plant->isOwnedBy($ownerId)) {
            throw EntityOwnershipException::fromEntityClassNameEntityIdAndUserId(
                Plant::class,
                $plant->getId(),
                $ownerId,
            );
        }
    }
}
