<?php

declare(strict_types=1);

namespace GardenManager\Seller\Domain;

use GardenManager\Shared\Domain\Pagination\PaginatedResult;
use Symfony\Component\Uid\Ulid;

interface SellerRepositoryInterface
{
    public function getById(Ulid $id): Seller;

    /** @return PaginatedResult<Seller> */
    public function findByOwnerIdPaginated(Ulid $ownerId, int $page, int $perPage): PaginatedResult;

    public function save(Seller $seller): void;
}
