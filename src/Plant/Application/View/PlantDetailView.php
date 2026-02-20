<?php

declare(strict_types=1);

namespace GardenManager\Plant\Application\View;

use DateTimeImmutable;
use GardenManager\Plant\Domain\Entity\Plant;
use GardenManager\Plant\Domain\Enum\LifecycleEnum;
use Symfony\Component\Uid\Ulid;

final class PlantDetailView
{
    public function __construct(
        public Ulid $id,
        public string $localName,
        public bool $isHybrid,
        public LifecycleEnum $lifecycle,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
        public ?string $genus = null,
        public ?string $epithet = null,
        public ?string $cultivar = null,
    ) {
    }

    public static function fromEntity(Plant $plant): self
    {
        return new self(
            id: $plant->getId(),
            localName: $plant->getLocalName(),
            isHybrid: $plant->isHybrid(),
            lifecycle: $plant->getLifecycle(),
            createdAt: $plant->getCreatedAt(),
            updatedAt: $plant->getUpdatedAt(),
            genus: $plant->getGenus(),
            epithet: $plant->getEpithet(),
            cultivar: $plant->getCultivar(),
        );
    }
}
