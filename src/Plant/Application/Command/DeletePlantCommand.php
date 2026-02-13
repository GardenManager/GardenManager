<?php

declare(strict_types=1);

namespace GardenManager\Plant\Application\Command;

use GardenManager\Shared\Application\CommandInterface;
use Symfony\Component\Uid\Ulid;

final class DeletePlantCommand implements CommandInterface
{
    public function __construct(
        public Ulid $plantId,
        public Ulid $ownerId,
    ) {
    }
}
