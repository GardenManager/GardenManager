<?php

declare(strict_types=1);

namespace GardenManager\Shared\Domain\Security;

use Symfony\Component\Uid\Ulid;

interface ActiveTenantProviderInterface
{
    public function getActiveTenantId(): Ulid;

    public function setActiveTenantId(Ulid $tenantId): void;

    public function hasActiveTenant(): bool;
}
