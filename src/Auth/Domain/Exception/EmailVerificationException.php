<?php

declare(strict_types=1);

namespace GardenManager\Auth\Domain\Exception;

use GardenManager\Shared\Domain\Exception\CoreException;

final class EmailVerificationException extends CoreException
{
    public static function missingUserId(): self
    {
        return new self('User id is missing from the request!', [], 404);
    }
}
