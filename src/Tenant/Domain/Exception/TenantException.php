<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Domain\Exception;

use GardenManager\Shared\Domain\Exception\CoreException;
use Symfony\Component\Uid\Ulid;

final class TenantException extends CoreException
{
    public const int CODE_ALREADY_MEMBER = 0x79ECEF04;
    public const int CODE_CANT_REMOVE_LAST_OWNER = 0x78751676;
    public const int CODE_INVITEE_NOT_FOUND = 0xA3B1C2D4;
    public const int CODE_NOT_MEMBER = 0x9F7296DA;
    public const int CODE_ONLY_OWNERS_CAN_MANAGE = 0xE8E321F2;

    public static function inviteeNotFound(string $email): self
    {
        $message = 'No user found with this email address.';

        return new self(
            message: $message,
            context: [
                'email' => $email,
            ],
            httpStatusCode: 404,
            code: self::CODE_INVITEE_NOT_FOUND,
            userFacingMessage: $message,
        );
    }

    public static function alreadyAMember(Ulid $tenantId, string $email): self
    {
        $message = 'This user is already a member of the tenant.';

        return new self(
            message: $message,
            context: [
                'tenantId' => $tenantId,
                'email' => $email,
            ],
            httpStatusCode: 409,
            code: self::CODE_ALREADY_MEMBER,
            userFacingMessage: $message,
        );
    }

    public static function cannotRemoveLastOwner(Ulid $tenantId): self
    {
        $message = 'Cannot remove the last owner of the tenant.';

        return new self(
            message: $message,
            context: [
                'tenantId' => $tenantId,
            ],
            httpStatusCode: 403,
            code: self::CODE_CANT_REMOVE_LAST_OWNER,
            userFacingMessage: $message,
        );
    }

    public static function notAMember(Ulid $tenantId, Ulid $userId): self
    {
        $message = 'The user is not a member of this tenant.';

        return new self(
            message: $message,
            context: [
                'tenantId' => $tenantId,
                'userId' => $userId,
            ],
            httpStatusCode: 404,
            code: self::CODE_NOT_MEMBER,
            userFacingMessage: $message,
        );
    }
}
