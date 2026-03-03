<?php

declare(strict_types=1);

namespace GardenManager\Permission\Domain\Exception;

use GardenManager\Shared\Domain\Exception\CoreException;
use Symfony\Component\Uid\Ulid;

final class PermissionException extends CoreException
{
    public const int CODE_ACCESS_DENIED = 0xA16B7C9D;
    public const int CODE_GROUP_NOT_FOUND = 0xE9C4CB39;
    public const int CODE_INVALID_CONFIG = 0xB28868CC;
    public const int CODE_CONCURRENT_MODIFICATION = 0xD47A2E51;

    public static function accessDenied(string $permission, Ulid $userId): self
    {
        return new self(
            message: \sprintf('Access denied: user %s lacks permission "%s"', $userId, $permission),
            context: [
                'permission' => $permission,
                'userId' => $userId->toString(),
            ],
            httpStatusCode: 403,
            code: self::CODE_ACCESS_DENIED,
            userFacingMessage: 'You do not have permission to perform this action.',
        );
    }

    public static function groupNotFound(): self
    {
        $message = 'Permission group not found.';

        return new self(
            message: $message,
            httpStatusCode: 404,
            code: self::CODE_GROUP_NOT_FOUND,
            userFacingMessage: $message,
        );
    }

    public static function concurrentModification(): self
    {
        $message = 'The permission configuration was modified by another user. Please try again.';

        return new self(
            message: $message,
            httpStatusCode: 409,
            code: self::CODE_CONCURRENT_MODIFICATION,
            userFacingMessage: $message,
        );
    }

    /**
     * @param list<string> $errors
     */
    public static function invalidConfig(array $errors): self
    {
        return new self(
            message: \sprintf('Invalid permission config: %s', implode('; ', $errors)),
            context: ['errors' => $errors],
            httpStatusCode: 400,
            code: self::CODE_INVALID_CONFIG,
            userFacingMessage: 'The permission configuration is invalid. ' . implode(' ', $errors),
        );
    }
}
