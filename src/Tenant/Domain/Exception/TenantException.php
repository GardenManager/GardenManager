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
        return new self(
            'No user found with this email address.',
            [
                'email' => $email,
            ],
            404,
            self::CODE_INVITEE_NOT_FOUND,
        );
    }

    public static function alreadyAMember(Ulid $tenantId, string $email): self
    {
        return new self(
            'This user is already a member of the tenant.',
            [
                'tenantId' => $tenantId,
                'email' => $email,
            ],
            409,
            self::CODE_ALREADY_MEMBER,
        );
    }

    public static function cannotRemoveLastOwner(Ulid $tenantId): self
    {
        return new self(
            'Cannot remove the last owner of the tenant.',
            [
                'tenantId' => $tenantId,
            ],
            403,
            self::CODE_CANT_REMOVE_LAST_OWNER,
        );
    }

    public static function notAMember(Ulid $tenantId, Ulid $userId): self
    {
        return new self(
            'The user is not a member of this tenant.',
            [
                'tenantId' => $tenantId,
                'userId' => $userId,
            ],
            404,
            self::CODE_NOT_MEMBER,
        );
    }

    public static function onlyOwnersCanManage(Ulid $tenantId, Ulid $actorUserId): self
    {
        return new self(
            'Only tenant owners can manage members.',
            [
                'tenantId' => $tenantId,
                'actorUserId' => $actorUserId,
            ],
            403,
            self::CODE_ONLY_OWNERS_CAN_MANAGE,
        );
    }
}
