<?php

namespace GardenManager\Auth\Application;

use GardenManager\Auth\Domain\AuthUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Ulid;

interface EmailVerificationServiceInterface
{
    public function sendVerificationEmail(AuthUser $user): void;

    public function validateEmailConfirmation(Request $request): Ulid;
}
