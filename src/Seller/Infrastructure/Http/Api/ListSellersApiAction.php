<?php

namespace GardenManager\Seller\Infrastructure\Http\Api;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Seller\Application\Dto\Api\SellerApiResponse;
use GardenManager\Seller\Application\Query\ListSellersQuery;
use GardenManager\Shared\Infrastructure\Bus\QueryDispatcher;
use GardenManager\Shared\Infrastructure\Http\PaginatedApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class ListSellersApiAction extends AbstractController
{
    public function __construct(
        private readonly QueryDispatcher $queryDispatcher,
    ) {
    }

    #[Route('/api/sellers', name: 'api_seller_list', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        /** @var AuthUser $user */
        $user = $this->getUser();

        $page = $request->query->getInt('page', 1);
        $limit = min(100, max(1, $request->query->getInt('limit', ListSellersQuery::DEFAULT_LIMIT)));

        $pager = $this->queryDispatcher->query(new ListSellersQuery($user->getId(), $page, $limit));

        $items = array_map(SellerApiResponse::fromView(...), $pager->items);

        return $this->json(PaginatedApiResponse::fromPaginatedResult($pager, $items));
    }
}
