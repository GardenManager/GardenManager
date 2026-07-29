<?php

declare(strict_types=1);

namespace GardenManager\Auth\Application;

use GardenManager\Auth\Domain\Entity\AuthUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Ulid;

interface EmailVerificationServiceInterface
{
    public function sendVerificationEmail(AuthUser $user): void;

    public function validateEmailConfirmation(Request $request): Ulid;
}
