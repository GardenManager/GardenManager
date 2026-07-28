<?php

declare(strict_types=1);

namespace GardenManager\Tests\Tenant\Application\Command;

use GardenManager\Tenant\Application\Command\UpdateTenantCommand;
use GardenManager\Tenant\Application\Command\UpdateTenantCommandHandler;
use GardenManager\Tenant\Domain\Tenant;
use GardenManager\Tenant\Domain\TenantRepositoryInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class UpdateTenantCommandHandlerTest extends TestCase
{
    #[Test]
    public function updatesTenantName(): void
    {
        $tenantId = new Ulid();
        $actorUserId = new Ulid();

        $tenant = Tenant::create(name: 'Old Name', id: $tenantId);

        $tenantRepo = $this->createMock(TenantRepositoryInterface::class);
        $tenantRepo->method('getById')->with($tenantId)->willReturn($tenant);
        $tenantRepo->expects(self::once())->method('save')->with($tenant);

        $handler = new UpdateTenantCommandHandler($tenantRepo);

        $handler(new UpdateTenantCommand(
            tenantId: $tenantId,
            name: 'New Name',
            actorUserId: $actorUserId,
        ));

        self::assertSame('New Name', $tenant->getName());
    }
}
