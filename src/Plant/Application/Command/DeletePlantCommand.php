<?php

namespace GardenManager\Plant\Application\Command;

use GardenManager\Shared\Application\CommandInterface;
use Symfony\Component\Uid\Ulid;

class DeletePlantCommand implements CommandInterface
{
    public function __construct(
        public Ulid $plantId,
        public Ulid$ownerId,
    )
    {
    }
}
