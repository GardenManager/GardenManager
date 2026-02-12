<?php

declare(strict_types=1);

namespace GardenManager\Shared\Domain\Exception;

use Symfony\Component\Uid\Ulid;

class EntityOwnershipException extends CoreException
{
    public static function fromEntityClassNameEntityIdAndUserId(
        string $entityClassName,
        Ulid $entityId,
        Ulid $userId
    ): self {
        return new self(
            sprintf(
                'The specified user not owns this %s!',
                array_last(explode('\\', $entityClassName))
            ),
            [
                'fullyQualifiedClassName' => $entityClassName,
                'entityId' => $entityId,
                'userId' => $userId
            ],
            403
        );
    }
}
