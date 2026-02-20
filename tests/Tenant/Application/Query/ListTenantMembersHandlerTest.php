<?php

declare(strict_types=1);

namespace GardenManager\Tests\Tenant\Application\Query;

use GardenManager\Tenant\Application\Dto\MemberUserInfoDto;
use GardenManager\Tenant\Application\Port\MemberUserResolverInterface;
use GardenManager\Tenant\Application\Query\ListTenantMembersQuery;
use GardenManager\Tenant\Application\Query\ListTenantMembersQueryHandler;
use GardenManager\Tenant\Domain\Enum\TenantMembershipRole;
use GardenManager\Tenant\Domain\Exception\TenantException;
use GardenManager\Tenant\Domain\Tenant;
use GardenManager\Tenant\Domain\TenantMembership;
use GardenManager\Tenant\Domain\TenantMembershipRepositoryInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class ListTenantMembersHandlerTest extends TestCase
{
    #[Test]
    public function returnsMemberViews(): void
    {
        $tenantId = new Ulid();
        $actorUserId = new Ulid();
        $memberId = new Ulid();

        $tenant = Tenant::create(name: 'Test Tenant', id: $tenantId);
        $actorMembership = TenantMembership::create(
            tenant: $tenant,
            userId: $actorUserId,
            role: TenantMembershipRole::OWNER,
        );
        $memberMembership = TenantMembership::create(
            tenant: $tenant,
            userId: $memberId,
            role: TenantMembershipRole::MEMBER,
        );

        $actorUser = new MemberUserInfoDto($actorUserId, 'owner@example.com', 'Owner');
        $memberUser = new MemberUserInfoDto($memberId, 'member@example.com', 'Member');

        $membershipRepo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $membershipRepo->method('findByTenantIdAndUserId')->willReturn($actorMembership);
        $membershipRepo->method('findByTenantId')->willReturn([$actorMembership, $memberMembership]);

        $resolver = $this->createStub(MemberUserResolverInterface::class);
        $resolver->method('resolveByIds')
            ->willReturn([
                (string) $actorUserId => $actorUser,
                (string) $memberId => $memberUser,
            ]);

        $handler = new ListTenantMembersQueryHandler($membershipRepo, $resolver);

        $result = $handler(new ListTenantMembersQuery(
            tenantId: $tenantId,
            actorUserId: $actorUserId,
        ));

        self::assertCount(2, $result);
        self::assertSame('owner@example.com', $result[0]->userEmail);
        self::assertSame(TenantMembershipRole::OWNER, $result[0]->role);
        self::assertSame('member@example.com', $result[1]->userEmail);
        self::assertSame(TenantMembershipRole::MEMBER, $result[1]->role);
    }

    #[Test]
    public function throwsWhenNotAMember(): void
    {
        $tenantId = new Ulid();
        $actorUserId = new Ulid();

        $membershipRepo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $membershipRepo->method('findByTenantIdAndUserId')->willReturn(null);

        $resolver = $this->createStub(MemberUserResolverInterface::class);

        $handler = new ListTenantMembersQueryHandler($membershipRepo, $resolver);

        $this->expectException(TenantException::class);

        $handler(new ListTenantMembersQuery(
            tenantId: $tenantId,
            actorUserId: $actorUserId,
        ));
    }

}
