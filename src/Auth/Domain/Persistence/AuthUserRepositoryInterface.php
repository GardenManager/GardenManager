<?php

declare(strict_types=1);

namespace GardenManager\Auth\Domain\Persistence;

use GardenManager\Auth\Domain\Entity\AuthUser;
use Symfony\Component\Uid\Ulid;

interface AuthUserRepositoryInterface
{
    public function findById(Ulid $id): ?AuthUser;

    public function getById(Ulid $id): AuthUser;

    /**
     * @param list<Ulid> $userIds
     *
     * @return array<string, AuthUser>
     */
    public function findByIds(array $userIds): array;

    public function findByEmail(string $email): ?AuthUser;

    public function save(AuthUser $user): void;
}
