<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\View;

use GardenManager\Tenant\Domain\TenantMembership;
use Symfony\Component\Uid\Ulid;

final readonly class UserTenantView
{
    public function __construct(
        public Ulid $tenantId,
        public string $tenantName,
        public bool $isOwner,
    ) {
    }

    public static function fromMembership(TenantMembership $membership): self
    {
        return new self(
            tenantId: $membership->getTenant()->getId(),
            tenantName: $membership->getTenant()->getName(),
            isOwner: $membership->isOwner(),
        );
    }
}
