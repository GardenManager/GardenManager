<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Domain;

use Symfony\Component\Uid\Ulid;

interface TenantRepositoryInterface
{
    public function getById(Ulid $id): Tenant;

    public function save(Tenant $tenant): void;
}
