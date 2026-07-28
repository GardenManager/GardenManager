<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Domain\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity]
#[ORM\Table(name: 'tenant_membership')]
#[ORM\UniqueConstraint(name: 'uq_tenant_id_user_id', columns: ['tenant_id', 'user_id'])]
#[ORM\Index(name: 'idx_tenant_id', columns: ['tenant_id'])]
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

    #[ORM\Column]
    private bool $isOwner;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    private function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public static function create(
        Tenant $tenant,
        Ulid $userId,
        bool $isOwner = false,
        ?Ulid $id = null,
    ): self {
        $membership = new self();
        $membership->id = $id ?? new Ulid();
        $membership->tenant = $tenant;
        $membership->userId = $userId;
        $membership->isOwner = $isOwner;

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

    public function getTenantId(): Ulid
    {
        return $this->tenant->getId();
    }

    public function getUserId(): Ulid
    {
        return $this->userId;
    }

    public function isOwner(): bool
    {
        return $this->isOwner;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
