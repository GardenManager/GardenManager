<?php

declare(strict_types=1);

namespace GardenManager\Auth\Domain\Persistence;

use GardenManager\Auth\Domain\Entity\AuthOidc;

interface AuthOidcRepositoryInterface
{
    public function findByProviderAndSubject(string $provider, string $subject): ?AuthOidc;

    public function save(AuthOidc $link): void;
}
