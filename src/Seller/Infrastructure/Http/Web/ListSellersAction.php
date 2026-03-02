<?php

declare(strict_types=1);

namespace GardenManager\Seller\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Seller\Application\Query\ListSellersQuery;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use GardenManager\Shared\Infrastructure\Bus\QueryDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ListSellersAction extends AbstractController
{
    public function __construct(
        private readonly QueryDispatcher $queryDispatcher,
        private readonly ActiveTenantProviderInterface $activeTenantProvider,
    ) {
    }

    #[Route('/sellers', name: 'seller_index', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        /** @var AuthUser $user */
        $user = $this->getUser();

        $tenantId = $this->activeTenantProvider->getActiveTenantId();
        $page = $request->query->getInt('page', 1);

        $pager = $this->queryDispatcher->query(new ListSellersQuery(
            actorUserId: $user->getId(),
            tenantId: $tenantId,
            page: $page,
        ));

        return $this->render('seller/index.html.twig', [
            'pager' => $pager,
        ]);
    }
}
