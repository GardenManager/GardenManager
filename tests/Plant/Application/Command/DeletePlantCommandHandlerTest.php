<?php

declare(strict_types=1);

namespace GardenManager\Tests\Plant\Application\Command;

use GardenManager\Plant\Application\Command\DeletePlantCommand;
use GardenManager\Plant\Application\Command\DeletePlantCommandHandler;
use GardenManager\Plant\Domain\Entity\Plant;
use GardenManager\Plant\Domain\Enum\LifecycleEnum;
use GardenManager\Plant\Domain\Persistence\PlantRepositoryInterface;
use GardenManager\Shared\Domain\Exception\EntityNotFoundException;
use GardenManager\Shared\Domain\Exception\TenantAccessException;
use GardenManager\Shared\Domain\Security\TenantAccessChecker;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class DeletePlantCommandHandlerTest extends TestCase
{
    #[Test]
    public function softDeletesPlant(): void
    {
        $tenantId = new Ulid();
        $plantId = new Ulid();

        $plant = Plant::create(
            tenantId: $tenantId,
            localName: 'Test',
            isHybrid: false,
            lifecycle: LifecycleEnum::ANNUAL,
            plantId: $plantId,
        );

        $repo = $this->createMock(PlantRepositoryInterface::class);
        $repo->method('getById')->with($plantId)->willReturn($plant);
        $repo->expects(self::once())->method('save');

        $handler = new DeletePlantCommandHandler($repo, new TenantAccessChecker());

        $handler(new DeletePlantCommand(
            plantId: $plantId,
            tenantId: $tenantId,
        ));

        self::assertTrue($plant->isDeleted());
    }

    #[Test]
    public function throwsNotFoundWhenPlantMissing(): void
    {
        $plantId = new Ulid();
        $tenantId = new Ulid();

        $repo = $this->createStub(PlantRepositoryInterface::class);
        $repo->method('getById')->willThrowException(
            EntityNotFoundException::fromEntityClassNameAndId(Plant::class, $plantId),
        );

        $handler = new DeletePlantCommandHandler($repo, new TenantAccessChecker());

        $this->expectException(EntityNotFoundException::class);

        $handler(new DeletePlantCommand(
            plantId: $plantId,
            tenantId: $tenantId,
        ));
    }

    #[Test]
    public function throwsAccessDeniedWhenNotTenant(): void
    {
        $plantId = new Ulid();
        $tenantId = new Ulid();
        $differentTenantId = new Ulid();

        $plant = Plant::create(
            tenantId: $differentTenantId,
            localName: 'Test',
            isHybrid: false,
            lifecycle: LifecycleEnum::ANNUAL,
            plantId: $plantId,
        );

        $repo = $this->createStub(PlantRepositoryInterface::class);
        $repo->method('getById')->willReturn($plant);

        $handler = new DeletePlantCommandHandler($repo, new TenantAccessChecker());

        $this->expectException(TenantAccessException::class);

        $handler(new DeletePlantCommand(
            plantId: $plantId,
            tenantId: $tenantId,
        ));
    }
}
