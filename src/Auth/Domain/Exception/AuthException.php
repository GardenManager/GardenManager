<?php

namespace GardenManager\Auth\Domain\Exception;

use GardenManager\Shared\Domain\Exception\CoreException;
use Symfony\Component\Uid\Ulid;

final class AuthException extends CoreException
{
    public static function userNotFoundById(Ulid $userId): self
    {
        return new self(
            'User not found by ID',
            [
                'userId' => $userId,
            ],
            404
        );
    }

    public static function userNotFoundByEmail(string $email): self
    {
        return new self(
            'User not found by email',
            [
                'email' => $email,
            ],
            404
        );
    }

    public static function invalidPassword(): self
    {
        return new self(
            'Invalid password',
            [],
            401
        );
    }

    public static function emailAlreadyRegistered(string $email): self
    {
        return new self(
            'Email already registered',
            [
                'email' => $email,
            ],
            409
        );
    }
}
