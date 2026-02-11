<?php

namespace GardenManager\Seller\Domain;

use GardenManager\Shared\Domain\Pagination\PaginatedResult;
use Symfony\Component\Uid\Ulid;

interface SellerRepositoryInterface
{
    public function findById(Ulid $id): ?Seller;

    public function getByIdForOwner(Ulid $id, Ulid $ownerId): Seller;

    /** @return PaginatedResult<Seller> */
    public function findByOwnerIdPaginated(Ulid $ownerId, int $page, int $perPage): PaginatedResult;

    public function save(Seller $seller): void;
}
