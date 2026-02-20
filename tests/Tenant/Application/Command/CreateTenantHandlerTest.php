<?php

declare(strict_types=1);

namespace GardenManager\Tests\Tenant\Application\Command;

use GardenManager\Tenant\Application\Command\CreateTenantCommand;
use GardenManager\Tenant\Application\Command\CreateTenantCommandHandler;
use GardenManager\Tenant\Domain\Enum\TenantMembershipRole;
use GardenManager\Tenant\Domain\Tenant;
use GardenManager\Tenant\Domain\TenantMembership;
use GardenManager\Tenant\Domain\TenantMembershipRepositoryInterface;
use GardenManager\Tenant\Domain\TenantRepositoryInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class CreateTenantHandlerTest extends TestCase
{
    #[Test]
    public function createsTenantAndOwnerMembership(): void
    {
        $tenantId = new Ulid();
        $ownerUserId = new Ulid();
        $savedTenant = null;
        $savedMembership = null;

        $tenantRepo = $this->createMock(TenantRepositoryInterface::class);
        $tenantRepo->expects(self::once())
            ->method('save')
            ->willReturnCallback(static function (Tenant $tenant) use (&$savedTenant): void {
                $savedTenant = $tenant;
            });

        $membershipRepo = $this->createMock(TenantMembershipRepositoryInterface::class);
        $membershipRepo->expects(self::once())
            ->method('save')
            ->willReturnCallback(static function (TenantMembership $membership) use (&$savedMembership): void {
                $savedMembership = $membership;
            });

        $handler = new CreateTenantCommandHandler($tenantRepo, $membershipRepo);

        $handler(new CreateTenantCommand(
            tenantId: $tenantId,
            name: 'My Garden',
            userId: $ownerUserId,
        ));

        self::assertInstanceOf(Tenant::class, $savedTenant);
        self::assertTrue($tenantId->equals($savedTenant->getId()));
        self::assertSame('My Garden', $savedTenant->getName());

        self::assertInstanceOf(TenantMembership::class, $savedMembership);
        self::assertTrue($ownerUserId->equals($savedMembership->getUserId()));
        self::assertSame(TenantMembershipRole::OWNER, $savedMembership->getRole());
    }
}
