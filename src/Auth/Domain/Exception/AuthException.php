<?php

declare(strict_types=1);

namespace GardenManager\Auth\Domain\Exception;

use GardenManager\Shared\Domain\Exception\CoreException;
use Symfony\Component\Uid\Ulid;

final class AuthException extends CoreException
{
    public const int CODE_NOT_FOUND_BY_ID = 0x49858514;
    public const int CODE_NOT_FOUND_BY_EMAIL = 0xF0B66257;
    public const int CODE_INVALID_PASSWORD = 0x734DA4A8;
    public const int CODE_EMAIL_ALREADY_REGISTERED = 0x18EC5DAF;

    public static function userNotFoundById(Ulid $userId): self
    {
        return new self(
            'User not found by ID',
            [
                'userId' => $userId,
            ],
            404,
            self::CODE_NOT_FOUND_BY_ID,
        );
    }

    public static function userNotFoundByEmail(string $email): self
    {
        return new self(
            'User not found by email',
            [
                'email' => $email,
            ],
            404,
            self::CODE_NOT_FOUND_BY_EMAIL,
        );
    }

    public static function invalidPassword(): self
    {
        return new self(
            'Invalid password',
            [],
            401,
            self::CODE_INVALID_PASSWORD,
        );
    }

    public static function emailAlreadyRegistered(string $email): self
    {
        return new self(
            'Email already registered',
            [
                'email' => $email,
            ],
            409,
            self::CODE_EMAIL_ALREADY_REGISTERED,
        );
    }
}
