<?php

namespace GardenManager\Plant\Application\Query;

use GardenManager\Shared\Application\QueryInterface;
use Symfony\Component\Uid\Ulid;

/** @implements QueryInterface<PlantDetailView> */
final readonly class GetPlantQuery implements QueryInterface
{
    public function __construct(
        public Ulid $plantId,
        public Ulid $ownerId
    )
    {
    }
}
