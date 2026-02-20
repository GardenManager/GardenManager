<?php

declare(strict_types=1);

namespace GardenManager\Seller\Application\Command;

use GardenManager\Shared\Application\CommandInterface;
use Symfony\Component\Uid\Ulid;

final readonly class DeleteSellerCommand implements CommandInterface
{
    public function __construct(
        public Ulid $sellerId,
        public Ulid $tenantId,
    ) {
    }
}
