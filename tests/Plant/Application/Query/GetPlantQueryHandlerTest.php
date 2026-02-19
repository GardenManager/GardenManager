<?php

declare(strict_types=1);

namespace GardenManager\Tests\Plant\Application\Query;

use GardenManager\Plant\Application\Query\GetPlantQuery;
use GardenManager\Plant\Application\Query\GetPlantQueryHandler;
use GardenManager\Plant\Domain\Entity\Plant;
use GardenManager\Plant\Domain\Enum\LifecycleEnum;
use GardenManager\Plant\Domain\Persistence\PlantRepositoryInterface;
use GardenManager\Plant\Domain\Security\PlantAccessChecker;
use GardenManager\Shared\Domain\Exception\EntityNotFoundException;
use GardenManager\Shared\Domain\Exception\EntityOwnershipException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class GetPlantQueryHandlerTest extends TestCase
{
    #[Test]
    public function returnsPlantDetailView(): void
    {
        $ownerId = new Ulid();
        $plantId = new Ulid();

        $plant = Plant::create(
            ownerId: $ownerId,
            localName: 'Test Plant',
            isHybrid: true,
            lifecycle: LifecycleEnum::PERENNIAL,
            genus: 'Rosa',
            epithet: 'gallica',
            cultivar: 'Officinalis',
            plantId: $plantId,
        );

        $repo = $this->createStub(PlantRepositoryInterface::class);
        $repo->method('getById')->with($plantId)->willReturn($plant);

        $handler = new GetPlantQueryHandler($repo, new PlantAccessChecker());

        $result = $handler(new GetPlantQuery(
            plantId: $plantId,
            ownerId: $ownerId,
        ));

        self::assertSame('Test Plant', $result->localName);
        self::assertTrue($result->isHybrid);
        self::assertSame(LifecycleEnum::PERENNIAL, $result->lifecycle);
        self::assertSame('Rosa', $result->genus);
        self::assertSame('gallica', $result->epithet);
        self::assertSame('Officinalis', $result->cultivar);
    }

    #[Test]
    public function throwsNotFoundWhenPlantMissing(): void
    {
        $plantId = new Ulid();
        $ownerId = new Ulid();

        $repo = $this->createStub(PlantRepositoryInterface::class);
        $repo->method('getById')->willThrowException(
            EntityNotFoundException::fromEntityClassNameAndId(Plant::class, $plantId),
        );

        $handler = new GetPlantQueryHandler($repo, new PlantAccessChecker());

        $this->expectException(EntityNotFoundException::class);

        $handler(new GetPlantQuery(
            plantId: $plantId,
            ownerId: $ownerId,
        ));
    }

    #[Test]
    public function throwsAccessDeniedWhenNotOwner(): void
    {
        $plantId = new Ulid();
        $ownerId = new Ulid();
        $differentOwnerId = new Ulid();

        $plant = Plant::create(
            ownerId: $differentOwnerId,
            localName: 'Test',
            isHybrid: false,
            lifecycle: LifecycleEnum::ANNUAL,
            plantId: $plantId,
        );

        $repo = $this->createStub(PlantRepositoryInterface::class);
        $repo->method('getById')->willReturn($plant);

        $handler = new GetPlantQueryHandler($repo, new PlantAccessChecker());

        $this->expectException(EntityOwnershipException::class);

        $handler(new GetPlantQuery(
            plantId: $plantId,
            ownerId: $ownerId,
        ));
    }
}
