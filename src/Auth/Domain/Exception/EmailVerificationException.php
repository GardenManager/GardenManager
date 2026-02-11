<?php

namespace GardenManager\Auth\Domain\Exception;

use GardenManager\Shared\Domain\Exception\CoreException;

class EmailVerificationException extends CoreException
{
    public static function missingUserId(): self
    {
        return new self('User id is missing from the request!', [], 404);
    }
}
