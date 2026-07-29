<?php

declare(strict_types=1);

namespace GardenManager\Seller\Domain\Persistence;

use GardenManager\Seller\Domain\Entity\Seller;
use GardenManager\Shared\Domain\Pagination\PaginatedResult;
use Symfony\Component\Uid\Ulid;

interface SellerRepositoryInterface
{
    public function getById(Ulid $id): Seller;

    /** @return PaginatedResult<Seller> */
    public function findPaginated(int $page, int $perPage): PaginatedResult;

    public function save(Seller $seller): void;
}
