<?php

declare(strict_types=1);

namespace GardenManager\Seller\Application\Query;

use GardenManager\Seller\Application\View\SellerDetailView;
use GardenManager\Shared\Application\QueryInterface;
use Symfony\Component\Uid\Ulid;

/** @implements QueryInterface<SellerDetailView> */
final readonly class GetSellerQuery implements QueryInterface
{
    public function __construct(
        public Ulid $sellerId,
        public Ulid $tenantId,
    ) {
    }
}
