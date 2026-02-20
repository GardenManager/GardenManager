<?php

declare(strict_types=1);

namespace GardenManager\Shared\Domain;

use Symfony\Component\Uid\Ulid;

interface TenantScoped
{
    public function getId(): Ulid;

    public function getTenantId(): Ulid;
}
