<?php

namespace GardenManager\Seller\Application\Query;

use GardenManager\Shared\Application\QueryInterface;
use Symfony\Component\Uid\Ulid;

/** @implements QueryInterface<SellerDetailView> */
final readonly class GetSellerQuery implements QueryInterface
{
    public function __construct(
        public Ulid $sellerId,
        public Ulid $ownerId,
    ) {
    }
}
