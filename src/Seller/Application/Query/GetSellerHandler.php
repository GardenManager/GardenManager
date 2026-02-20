<?php

declare(strict_types=1);

namespace GardenManager\Seller\Application\Query;

use GardenManager\Seller\Application\View\SellerDetailView;
use GardenManager\Seller\Domain\SellerRepositoryInterface;
use GardenManager\Shared\Domain\Security\TenantAccessChecker;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class GetSellerHandler
{
    public function __construct(
        private SellerRepositoryInterface $sellerRepository,
        private TenantAccessChecker $tenantAccessChecker,
    ) {
    }

    public function __invoke(GetSellerQuery $query): SellerDetailView
    {
        $seller = $this->sellerRepository->getById($query->sellerId);
        $this->tenantAccessChecker->ensureTenantAccess($seller, $query->tenantId);

        return SellerDetailView::fromEntity($seller);
    }
}
