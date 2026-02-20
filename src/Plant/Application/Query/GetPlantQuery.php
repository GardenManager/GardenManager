<?php

declare(strict_types=1);

namespace GardenManager\Plant\Application\Query;

use GardenManager\Plant\Application\View\PlantDetailView;
use GardenManager\Shared\Application\QueryInterface;
use Symfony\Component\Uid\Ulid;

/** @implements QueryInterface<PlantDetailView> */
final readonly class GetPlantQuery implements QueryInterface
{
    public function __construct(
        public Ulid $plantId,
        public Ulid $tenantId,
    ) {
    }
}
