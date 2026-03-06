<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Domain\Exception;

use GardenManager\Shared\Domain\Exception\CoreException;
use Symfony\Component\Uid\Ulid;

final class CustomAttributeException extends CoreException
{
    public static function duplicateName(string $entityType, string $name): self
    {
        return new self(
            message: sprintf('A custom attribute with name "%s" already exists for entity type "%s".', $name, $entityType),
            context: [
                'entityType' => $entityType,
                'name' => $name,
            ],
            httpStatusCode: 409,
            userFacingMessage: 'A custom attribute with that name already exist for this type.'
        );
    }

    public static function definitionNotFound(Ulid $definitionId): self
    {
        return new self(
            message: 'The custom attribute definition cannot be found.',
            context:[
                'definitionId' => $definitionId
            ],
            httpStatusCode: 404,
        );
    }
}
