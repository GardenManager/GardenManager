<?php

declare(strict_types=1);

namespace GardenManager\Shared\Domain\Exception;

use Symfony\Component\Uid\Ulid;

final class EntityNotFoundException extends CoreException
{
    public static function fromEntityClassNameAndId(string $entityClassName, Ulid $entityId): self
    {
        return new self(
            \sprintf(
                'The %s cannot be found by it\'s ID!',
                array_last(explode('\\', $entityClassName)),
            ),
            [
                'fullyQualifiedClassName' => $entityClassName,
                'entityId' => $entityId,
            ],
            404,
        );
    }
}
