<?php

declare(strict_types=1);

namespace GardenManager\Auth\Domain\Exception;

use GardenManager\Shared\Domain\Exception\CoreException;
use Throwable;

final class EmailVerificationException extends CoreException
{
    public static function missingUserId(): self
    {
        return new self(
            message: 'User id is missing from the request!',
            httpStatusCode: 404,
            userFacingMessage: 'The verification link is invalid or has expired.',
        );
    }

    public static function invalidVerificationLink(Throwable $previous): self
    {
        return new self(
            message: 'Email verification link validation failed.',
            httpStatusCode: 400,
            previous: $previous,
            userFacingMessage: 'The verification link is invalid or has expired.',
        );
    }
}
