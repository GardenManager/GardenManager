<?php

declare(strict_types=1);

namespace GardenManager\Tests\Plant\Application\Command;

use GardenManager\Plant\Application\Command\UpdatePlantCommand;
use GardenManager\Plant\Application\Command\UpdatePlantCommandHandler;
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
final class UpdatePlantCommandHandlerTest extends TestCase
{
    #[Test]
    public function updatesPlant(): void
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

        $repo = $this->createMock(PlantRepositoryInterface::class);
        $repo->method('getById')->with($plantId)->willReturn($plant);
        $repo->expects(self::once())->method('save');

        $handler = new UpdatePlantCommandHandler($repo, new PlantAccessChecker());

        $command = new UpdatePlantCommand(
            plantId: $plantId,
            ownerId: $ownerId,
            localName: 'New Name',
            isHybrid: true,
            lifecycle: LifecycleEnum::PERENNIAL,
            genus: 'Mentha',
        );

        $handler($command);

        self::assertSame('New Name', $plant->getLocalName());
        self::assertTrue($plant->isHybrid());
        self::assertSame(LifecycleEnum::PERENNIAL, $plant->getLifecycle());
        self::assertSame('Mentha', $plant->getGenus());
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

        $handler = new UpdatePlantCommandHandler($repo, new PlantAccessChecker());

        $this->expectException(EntityNotFoundException::class);

        $handler(new UpdatePlantCommand(
            plantId: $plantId,
            ownerId: $ownerId,
            localName: 'Test',
            isHybrid: false,
            lifecycle: LifecycleEnum::ANNUAL,
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

        $handler = new UpdatePlantCommandHandler($repo, new PlantAccessChecker());

        $this->expectException(EntityOwnershipException::class);

        $handler(new UpdatePlantCommand(
            plantId: $plantId,
            ownerId: $ownerId,
            localName: 'Hacked',
            isHybrid: false,
            lifecycle: LifecycleEnum::ANNUAL,
        ));
    }
}
