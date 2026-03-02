<?php

declare(strict_types=1);

namespace GardenManager\Tests\Plant\Application\Command;

use GardenManager\Plant\Application\Command\UpdatePlantCommand;
use GardenManager\Plant\Application\Command\UpdatePlantCommandHandler;
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
final class UpdatePlantCommandHandlerTest extends TestCase
{
    #[Test]
    public function updatesPlant(): void
    {
        $tenantId = new Ulid();
        $plantId = new Ulid();

        $plant = Plant::create(
            tenantId: $tenantId,
            localName: 'Old Name',
            isHybrid: false,
            lifecycle: LifecycleEnum::ANNUAL,
            plantId: $plantId,
        );

        $repo = $this->createMock(PlantRepositoryInterface::class);
        $repo->method('getById')->with($plantId)->willReturn($plant);
        $repo->expects(self::once())->method('save');

        $handler = new UpdatePlantCommandHandler($repo, new TenantAccessChecker());

        $command = new UpdatePlantCommand(
            plantId: $plantId,
            tenantId: $tenantId,
            actorUserId: new Ulid(),
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
        $tenantId = new Ulid();

        $repo = $this->createStub(PlantRepositoryInterface::class);
        $repo->method('getById')->willThrowException(
            EntityNotFoundException::fromEntityClassNameAndId(Plant::class, $plantId),
        );

        $handler = new UpdatePlantCommandHandler($repo, new TenantAccessChecker());

        $this->expectException(EntityNotFoundException::class);

        $handler(new UpdatePlantCommand(
            plantId: $plantId,
            tenantId: $tenantId,
            actorUserId: new Ulid(),
            localName: 'Test',
            isHybrid: false,
            lifecycle: LifecycleEnum::ANNUAL,
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

        $handler = new UpdatePlantCommandHandler($repo, new TenantAccessChecker());

        $this->expectException(TenantAccessException::class);

        $handler(new UpdatePlantCommand(
            plantId: $plantId,
            tenantId: $tenantId,
            actorUserId: new Ulid(),
            localName: 'Hacked',
            isHybrid: false,
            lifecycle: LifecycleEnum::ANNUAL,
        ));
    }
}
