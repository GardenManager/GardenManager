<?php

namespace GardenManager\Auth\Domain;

use Symfony\Component\Uid\Ulid;

interface AuthUserRepositoryInterface
{
    public function findById(Ulid $id): ?AuthUser;

    public function findByEmail(string $email): ?AuthUser;

    public function save(AuthUser $user): void;
}
