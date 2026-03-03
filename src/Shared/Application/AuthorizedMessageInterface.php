<?php

declare(strict_types=1);

namespace GardenManager\Shared\Application;

use Symfony\Component\Uid\Ulid;

interface AuthorizedMessageInterface
{
    public function getActorUserId(): Ulid;

    public function getTenantId(): Ulid;
}
