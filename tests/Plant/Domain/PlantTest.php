<?php

declare(strict_types=1);

namespace GardenManager\Tests\Plant\Domain;

use GardenManager\Plant\Domain\Entity\Plant;
use GardenManager\Plant\Domain\Enum\LifecycleEnum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

final class PlantTest extends TestCase
{
    #[Test]
    public function createsPlantWithAllFields(): void
    {
        $ownerId = new Ulid();
        $plantId = new Ulid();

        $plant = Plant::create(
            ownerId: $ownerId,
            localName: 'Tomato',
            isHybrid: true,
            lifecycle: LifecycleEnum::ANNUAL,
            genus: 'Solanum',
            epithet: 'lycopersicum',
            cultivar: 'Roma',
            plantId: $plantId,
        );

        self::assertTrue($plantId->equals($plant->getId()));
        self::assertTrue($ownerId->equals($plant->getOwnerId()));
        self::assertSame('Tomato', $plant->getLocalName());
        self::assertTrue($plant->isHybrid());
        self::assertSame(LifecycleEnum::ANNUAL, $plant->getLifecycle());
        self::assertSame('Solanum', $plant->getGenus());
        self::assertSame('lycopersicum', $plant->getEpithet());
        self::assertSame('Roma', $plant->getCultivar());
        self::assertNotNull($plant->getCreatedAt());
    }

    #[Test]
    public function createsPlantWithGeneratedId(): void
    {
        $plant = Plant::create(
            ownerId: new Ulid(),
            localName: 'Basil',
            isHybrid: false,
            lifecycle: LifecycleEnum::ANNUAL,
        );

        self::assertInstanceOf(Ulid::class, $plant->getId());
    }

    #[Test]
    public function createsPlantWithProvidedId(): void
    {
        $plantId = new Ulid();

        $plant = Plant::create(
            ownerId: new Ulid(),
            localName: 'Basil',
            isHybrid: false,
            lifecycle: LifecycleEnum::ANNUAL,
            plantId: $plantId,
        );

        self::assertTrue($plantId->equals($plant->getId()));
    }

    #[Test]
    public function updatesMutableFields(): void
    {
        $ownerId = new Ulid();
        $plantId = new Ulid();

        $plant = Plant::create(
            ownerId: $ownerId,
            localName: 'Old Name',
            isHybrid: false,
            lifecycle: LifecycleEnum::ANNUAL,
            plantId: $plantId,
        );

        $plant->update(
            plantId: $plantId,
            ownerId: $ownerId,
            localName: 'New Name',
            isHybrid: true,
            lifecycle: LifecycleEnum::PERENNIAL,
            genus: 'Mentha',
            epithet: 'spicata',
            cultivar: 'Spearmint',
        );

        self::assertSame('New Name', $plant->getLocalName());
        self::assertTrue($plant->isHybrid());
        self::assertSame(LifecycleEnum::PERENNIAL, $plant->getLifecycle());
        self::assertSame('Mentha', $plant->getGenus());
        self::assertSame('spicata', $plant->getEpithet());
        self::assertSame('Spearmint', $plant->getCultivar());
    }

    #[Test]
    public function softDeleteSetsTimestamp(): void
    {
        $plant = Plant::create(
            ownerId: new Ulid(),
            localName: 'Basil',
            isHybrid: false,
            lifecycle: LifecycleEnum::ANNUAL,
        );

        $plant->softDelete();

        self::assertTrue($plant->isDeleted());
        self::assertNotNull($plant->getDeletedAt());
    }

    #[Test]
    public function isNotDeletedByDefault(): void
    {
        $plant = Plant::create(
            ownerId: new Ulid(),
            localName: 'Basil',
            isHybrid: false,
            lifecycle: LifecycleEnum::ANNUAL,
        );

        self::assertFalse($plant->isDeleted());
    }

    #[Test]
    public function isOwnedByMatchingOwner(): void
    {
        $ownerId = new Ulid();

        $plant = Plant::create(
            ownerId: $ownerId,
            localName: 'Basil',
            isHybrid: false,
            lifecycle: LifecycleEnum::ANNUAL,
        );

        self::assertTrue($plant->isOwnedBy($ownerId));
    }

    #[Test]
    public function isNotOwnedByDifferentOwner(): void
    {
        $plant = Plant::create(
            ownerId: new Ulid(),
            localName: 'Basil',
            isHybrid: false,
            lifecycle: LifecycleEnum::ANNUAL,
        );

        self::assertFalse($plant->isOwnedBy(new Ulid()));
    }
}
