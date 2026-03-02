<?php

declare(strict_types=1);

namespace GardenManager\Tests\Permission\Application\Command;

use GardenManager\Permission\Application\Command\RemoveMemberPermissionOverrideCommand;
use GardenManager\Permission\Application\Command\RemoveMemberPermissionOverrideCommandHandler;
use GardenManager\Permission\Application\Service\PermissionCacheInvalidatorInterface;
use GardenManager\Permission\Domain\ValueObject\TenantPermissionConfig;
use GardenManager\Tenant\Domain\Tenant;
use GardenManager\Tenant\Domain\TenantRepositoryInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class RemoveMemberPermissionOverrideHandlerTest extends TestCase
{
    #[Test]
    public function removesOverrideSuccessfully(): void
    {
        $tenantId = new Ulid();
        $userId = new Ulid();

        $config = new TenantPermissionConfig(
            userOverrides: [(string) $userId => ['-plant.edit']],
        );

        $tenant = Tenant::create(name: 'Test', id: $tenantId);
        $tenant->updatePermissionsConfig($config);

        $tenantRepo = $this->createMock(TenantRepositoryInterface::class);
        $tenantRepo->method('getById')->willReturn($tenant);
        $tenantRepo->expects(self::once())->method('save');

        $handler = new RemoveMemberPermissionOverrideCommandHandler(
            $tenantRepo,
            $this->createStub(PermissionCacheInvalidatorInterface::class),
        );

        $handler(new RemoveMemberPermissionOverrideCommand(
            userId: $userId,
            permission: 'plant.edit',
            tenantId: $tenantId,
            actorUserId: new Ulid(),
        ));

        $overrides = $tenant->getPermissionsConfig()->getUserOverrides((string) $userId);
        self::assertSame([], $overrides);
    }
}
