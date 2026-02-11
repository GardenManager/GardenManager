<?php

declare(strict_types=1);

namespace GardenManager\Auth\Domain;

interface AuthOidcRepositoryInterface
{
    public function findByProviderAndSubject(string $provider, string $subject): ?AuthOidc;

    public function save(AuthOidc $link): void;
}
