<?php

namespace GardenManager\Seller\Application\Query;

use GardenManager\Seller\Domain\SellerRepositoryInterface;
use GardenManager\Shared\Domain\Pagination\PaginatedResult;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class ListSellersHandler
{
    public function __construct(
        private SellerRepositoryInterface $sellerRepository,
    ) {
    }

    /** @return PaginatedResult<SellerDetailView> */
    public function __invoke(ListSellersQuery $query): PaginatedResult
    {
        return $this->sellerRepository->findByOwnerIdPaginated(
            $query->getOwnerId(),
            $query->getPage(),
            $query->getLimit(),
        )->map(SellerDetailView::fromEntity(...));
    }
}
