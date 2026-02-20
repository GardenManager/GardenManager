<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Domain;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use GardenManager\Tenant\Domain\Enum\TenantMembershipRole;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: 'tenant_membership')]
#[ORM\UniqueConstraint(name: 'uniq_tenant_user', columns: ['tenant_id', 'user_id'])]
class TenantMembership
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(type: 'ulid', unique: true)]
    private Ulid $id;

    #[ORM\ManyToOne(targetEntity: Tenant::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Tenant $tenant;

    #[ORM\Column(type: 'ulid')]
    private Ulid $userId;

    #[ORM\Column(type: 'string', length: 16, enumType: TenantMembershipRole::class)]
    private TenantMembershipRole $role;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    private function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public static function create(
        Tenant $tenant,
        Ulid $userId,
        TenantMembershipRole $role,
        ?Ulid $id = null,
    ): self {
        $membership = new self();
        $membership->id = $id ?? new Ulid();
        $membership->tenant = $tenant;
        $membership->userId = $userId;
        $membership->role = $role;

        return $membership;
    }

    public function getId(): Ulid
    {
        return $this->id;
    }

    public function getTenant(): Tenant
    {
        return $this->tenant;
    }

    public function getUserId(): Ulid
    {
        return $this->userId;
    }

    public function getRole(): TenantMembershipRole
    {
        return $this->role;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
