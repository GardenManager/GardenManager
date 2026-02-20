<?php

declare(strict_types=1);

namespace GardenManager\Tests\Tenant\Application\Command;

use GardenManager\Tenant\Application\Command\InviteMemberCommand;
use GardenManager\Tenant\Application\Command\InviteMemberCommandHandler;
use GardenManager\Tenant\Application\Dto\MemberUserInfoDto;
use GardenManager\Tenant\Application\Port\MemberUserResolverInterface;
use GardenManager\Tenant\Domain\Enum\TenantMembershipRole;
use GardenManager\Tenant\Domain\Exception\TenantException;
use GardenManager\Tenant\Domain\Security\TenantAuthorizationChecker;
use GardenManager\Tenant\Domain\Tenant;
use GardenManager\Tenant\Domain\TenantMembership;
use GardenManager\Tenant\Domain\TenantMembershipRepositoryInterface;
use GardenManager\Tenant\Domain\TenantRepositoryInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class InviteMemberHandlerTest extends TestCase
{
    private Ulid $tenantId;
    private Ulid $actorUserId;
    private Tenant $tenant;
    private TenantMembership $actorMembership;

    protected function setUp(): void
    {
        $this->tenantId = new Ulid();
        $this->actorUserId = new Ulid();
        $this->tenant = Tenant::create(name: 'Test Tenant', id: $this->tenantId);
        $this->actorMembership = TenantMembership::create(
            tenant: $this->tenant,
            userId: $this->actorUserId,
            role: TenantMembershipRole::OWNER,
        );
    }

    #[Test]
    public function ownerCanInviteMember(): void
    {
        $inviteeId = new Ulid();
        $invitee = new MemberUserInfoDto($inviteeId, 'invitee@example.com', 'Invitee');
        $membershipId = new Ulid();
        $savedMembership = null;

        $membershipRepo = $this->createMock(TenantMembershipRepositoryInterface::class);
        $membershipRepo->method('findByTenantIdAndUserId')
            ->with($this->tenantId, $inviteeId)
            ->willReturn(null);
        $membershipRepo->expects(self::once())
            ->method('save')
            ->willReturnCallback(static function (TenantMembership $m) use (&$savedMembership): void {
                $savedMembership = $m;
            });

        $resolver = $this->createStub(MemberUserResolverInterface::class);
        $resolver->method('resolveByEmail')->willReturn($invitee);

        $tenantRepo = $this->createStub(TenantRepositoryInterface::class);
        $tenantRepo->method('getById')->willReturn($this->tenant);

        $checker = $this->createCheckerStub($this->actorMembership);
        $handler = new InviteMemberCommandHandler($tenantRepo, $membershipRepo, $resolver, $checker);

        $handler(new InviteMemberCommand(
            membershipId: $membershipId,
            tenantId: $this->tenantId,
            inviteeEmail: 'invitee@example.com',
            role: TenantMembershipRole::MEMBER,
            actorUserId: $this->actorUserId,
        ));

        self::assertInstanceOf(TenantMembership::class, $savedMembership);
        self::assertTrue($inviteeId->equals($savedMembership->getUserId()));
        self::assertSame(TenantMembershipRole::MEMBER, $savedMembership->getRole());
    }

    #[Test]
    public function nonOwnerCannotInvite(): void
    {
        $memberMembership = TenantMembership::create(
            tenant: $this->tenant,
            userId: $this->actorUserId,
            role: TenantMembershipRole::MEMBER,
        );

        $membershipRepo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $resolver = $this->createStub(MemberUserResolverInterface::class);
        $tenantRepo = $this->createStub(TenantRepositoryInterface::class);

        $checker = $this->createCheckerStub($memberMembership);
        $handler = new InviteMemberCommandHandler($tenantRepo, $membershipRepo, $resolver, $checker);

        $this->expectException(TenantException::class);

        $handler(new InviteMemberCommand(
            membershipId: new Ulid(),
            tenantId: $this->tenantId,
            inviteeEmail: 'invitee@example.com',
            role: TenantMembershipRole::MEMBER,
            actorUserId: $this->actorUserId,
        ));
    }

    #[Test]
    public function throwsWhenInviteeNotFound(): void
    {
        $membershipRepo = $this->createStub(TenantMembershipRepositoryInterface::class);

        $resolver = $this->createStub(MemberUserResolverInterface::class);
        $resolver->method('resolveByEmail')->willReturn(null);

        $tenantRepo = $this->createStub(TenantRepositoryInterface::class);

        $checker = $this->createCheckerStub($this->actorMembership);
        $handler = new InviteMemberCommandHandler($tenantRepo, $membershipRepo, $resolver, $checker);

        $this->expectException(TenantException::class);

        $handler(new InviteMemberCommand(
            membershipId: new Ulid(),
            tenantId: $this->tenantId,
            inviteeEmail: 'nonexistent@example.com',
            role: TenantMembershipRole::MEMBER,
            actorUserId: $this->actorUserId,
        ));
    }

    #[Test]
    public function throwsWhenUserAlreadyMember(): void
    {
        $inviteeId = new Ulid();
        $invitee = new MemberUserInfoDto($inviteeId, 'invitee@example.com', 'Invitee');
        $existingMembership = TenantMembership::create(
            tenant: $this->tenant,
            userId: $inviteeId,
            role: TenantMembershipRole::MEMBER,
        );

        $membershipRepo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $membershipRepo->method('findByTenantIdAndUserId')->willReturn($existingMembership);

        $resolver = $this->createStub(MemberUserResolverInterface::class);
        $resolver->method('resolveByEmail')->willReturn($invitee);

        $tenantRepo = $this->createStub(TenantRepositoryInterface::class);

        $checker = $this->createCheckerStub($this->actorMembership);
        $handler = new InviteMemberCommandHandler($tenantRepo, $membershipRepo, $resolver, $checker);

        $this->expectException(TenantException::class);

        $handler(new InviteMemberCommand(
            membershipId: new Ulid(),
            tenantId: $this->tenantId,
            inviteeEmail: 'invitee@example.com',
            role: TenantMembershipRole::MEMBER,
            actorUserId: $this->actorUserId,
        ));
    }

    private function createCheckerStub(?TenantMembership $actorMembership): TenantAuthorizationChecker
    {
        $repo = $this->createStub(TenantMembershipRepositoryInterface::class);
        $repo->method('findByTenantIdAndUserId')->willReturn($actorMembership);

        return new TenantAuthorizationChecker($repo);
    }
}
