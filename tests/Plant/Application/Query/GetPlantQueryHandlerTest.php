<?php

declare(strict_types=1);

namespace GardenManager\Tests\Plant\Application\Query;

use GardenManager\Plant\Application\Query\GetPlantQuery;
use GardenManager\Plant\Application\Query\GetPlantQueryHandler;
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
final class GetPlantQueryHandlerTest extends TestCase
{
    #[Test]
    public function returnsPlantDetailView(): void
    {
        $tenantId = new Ulid();
        $plantId = new Ulid();

        $plant = Plant::create(
            tenantId: $tenantId,
            localName: 'Test Plant',
            isHybrid: true,
            lifecycle: LifecycleEnum::PERENNIAL,
            genus: 'Rosa',
            epithet: 'gallica',
            cultivar: 'Officinalis',
            plantId: $plantId,
        );

        $repo = $this->createStub(PlantRepositoryInterface::class);
        $repo->method('getById')->willReturn($plant);

        $handler = new GetPlantQueryHandler($repo, new TenantAccessChecker());

        $result = $handler(new GetPlantQuery(
            plantId: $plantId,
            tenantId: $tenantId,
            actorUserId: new Ulid(),
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
        $tenantId = new Ulid();

        $repo = $this->createStub(PlantRepositoryInterface::class);
        $repo->method('getById')->willThrowException(
            EntityNotFoundException::fromEntityClassNameAndId(Plant::class, $plantId),
        );

        $handler = new GetPlantQueryHandler($repo, new TenantAccessChecker());

        $this->expectException(EntityNotFoundException::class);

        $handler(new GetPlantQuery(
            plantId: $plantId,
            tenantId: $tenantId,
            actorUserId: new Ulid(),
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

        $handler = new GetPlantQueryHandler($repo, new TenantAccessChecker());

        $this->expectException(TenantAccessException::class);

        $handler(new GetPlantQuery(
            plantId: $plantId,
            tenantId: $tenantId,
            actorUserId: new Ulid(),
        ));
    }
}
