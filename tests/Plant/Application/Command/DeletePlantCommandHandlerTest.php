<?php

declare(strict_types=1);

namespace GardenManager\Tests\Plant\Application\Command;

use GardenManager\Plant\Application\Command\DeletePlantCommand;
use GardenManager\Plant\Application\Command\DeletePlantCommandHandler;
use GardenManager\Plant\Domain\Entity\Plant;
use GardenManager\Plant\Domain\Enum\LifecycleEnum;
use GardenManager\Plant\Domain\Persistence\PlantRepositoryInterface;
use GardenManager\Plant\Domain\Security\PlantAccessChecker;
use GardenManager\Shared\Domain\Exception\EntityNotFoundException;
use GardenManager\Shared\Domain\Exception\EntityOwnershipException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

final class DeletePlantCommandHandlerTest extends TestCase
{
    #[Test]
    public function softDeletesPlant(): void
    {
        $ownerId = new Ulid();
        $plantId = new Ulid();

        $plant = Plant::create(
            ownerId: $ownerId,
            localName: 'Test',
            isHybrid: false,
            lifecycle: LifecycleEnum::ANNUAL,
            plantId: $plantId,
        );

        $repo = $this->createMock(PlantRepositoryInterface::class);
        $repo->method('getById')->with($plantId)->willReturn($plant);
        $repo->expects(self::once())->method('save');

        $handler = new DeletePlantCommandHandler($repo, new PlantAccessChecker());

        $handler(new DeletePlantCommand(
            plantId: $plantId,
            ownerId: $ownerId,
        ));

        self::assertTrue($plant->isDeleted());
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

        $handler = new DeletePlantCommandHandler($repo, new PlantAccessChecker());

        $this->expectException(EntityNotFoundException::class);

        $handler(new DeletePlantCommand(
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

        $handler = new DeletePlantCommandHandler($repo, new PlantAccessChecker());

        $this->expectException(EntityOwnershipException::class);

        $handler(new DeletePlantCommand(
            plantId: $plantId,
            ownerId: $ownerId,
        ));
    }
}
