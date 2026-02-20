<?php

declare(strict_types=1);

namespace GardenManager\Shared\Domain\Exception;

use Symfony\Component\Uid\Ulid;

final class TenantAccessException extends CoreException
{
    public static function fromEntityClassNameEntityIdAndTenantId(
        string $entityClassName,
        Ulid $entityId,
        Ulid $tenantId,
    ): self {
        return new self(
            \sprintf(
                'The active tenant does not have access to this %s!',
                array_last(explode('\\', $entityClassName)),
            ),
            [
                'fullyQualifiedClassName' => $entityClassName,
                'entityId' => $entityId,
                'tenantId' => $tenantId,
            ],
            403,
        );
    }
}
