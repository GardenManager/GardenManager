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
            message: 'User not found by ID',
            context: [
                'userId' => $userId,
            ],
            httpStatusCode: 404,
            code: self::CODE_NOT_FOUND_BY_ID,
        );
    }

    public static function userNotFoundByEmail(string $email): self
    {
        return new self(
            message: 'User not found by email',
            context: [
                'email' => $email,
            ],
            httpStatusCode: 404,
            code: self::CODE_NOT_FOUND_BY_EMAIL,
        );
    }

    public static function invalidPassword(): self
    {
        $message = 'Invalid password. Please try again.';

        return new self(
            message: $message,
            httpStatusCode: 401,
            code: self::CODE_INVALID_PASSWORD,
            userFacingMessage: $message,
        );
    }

    public static function emailAlreadyRegistered(string $email): self
    {
        $message = 'This email address is already registered.';

        return new self(
            message: $message,
            context: [
                'email' => $email,
            ],
            httpStatusCode: 409,
            code: self::CODE_EMAIL_ALREADY_REGISTERED,
            userFacingMessage: $message,
        );
    }
}
