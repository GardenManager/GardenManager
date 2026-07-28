<?php

declare(strict_types=1);

namespace GardenManager\Seller\Application\Query;

use GardenManager\Seller\Application\View\SellerDetailView;
use GardenManager\Seller\Domain\SellerRepositoryInterface;
use GardenManager\Shared\Domain\Pagination\PaginatedResult;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class ListSellersQueryHandler
{
    public function __construct(
        private SellerRepositoryInterface $sellerRepository,
    ) {
    }

    /** @return PaginatedResult<SellerDetailView> */
    public function __invoke(ListSellersQuery $query): PaginatedResult
    {
        return $this->sellerRepository->findPaginated(
            $query->getPage(),
            $query->getLimit(),
        )->map(SellerDetailView::fromEntity(...));
    }
}
