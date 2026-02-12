<?php

declare(strict_types=1);

namespace GardenManager\Seller\Application\Query;

use GardenManager\Seller\Domain\SellerAccessChecker;
use GardenManager\Seller\Domain\SellerRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class GetSellerHandler
{
    public function __construct(
        private SellerRepositoryInterface $sellerRepository,
        private SellerAccessChecker $sellerAccessChecker,
    ) {
    }

    public function __invoke(GetSellerQuery $query): SellerDetailView
    {
        $seller = $this->sellerRepository->getById($query->sellerId);
        $this->sellerAccessChecker->ensureOwnership($seller, $query->ownerId);

        return SellerDetailView::fromEntity($seller);
    }
}
