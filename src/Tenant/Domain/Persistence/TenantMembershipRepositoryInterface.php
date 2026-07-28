<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Domain\Persistence;

use GardenManager\Tenant\Domain\Entity\TenantMembership;
use Symfony\Component\Uid\Ulid;

interface TenantMembershipRepositoryInterface
{
    public function getById(Ulid $id): TenantMembership;

    public function findByTenantIdAndUserId(Ulid $tenantId, Ulid $userId): ?TenantMembership;

    /** @return list<TenantMembership> */
    public function findByTenantId(Ulid $tenantId): array;

    /** @return list<TenantMembership> */
    public function findByUserId(Ulid $userId): array;

    public function save(TenantMembership $membership): void;

    public function remove(TenantMembership $membership): void;
}
