<?php

declare(strict_types=1);

namespace GardenManager\Seller\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Seller\Application\Query\GetSellerQuery;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use GardenManager\Shared\Infrastructure\Bus\QueryDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Ulid;

final class ShowSellerAction extends AbstractController
{
    public function __construct(
        private readonly QueryDispatcher $queryDispatcher,
        private readonly ActiveTenantProviderInterface $activeTenantProvider,
    ) {
    }

    #[Route('/sellers/{id}', name: 'seller_show', methods: ['GET'], requirements: ['id' => '[0-9A-Z]{26}'])]
    public function __invoke(string $id): Response
    {
        /** @var AuthUser $user */
        $user = $this->getUser();

        $seller = $this->queryDispatcher->query(new GetSellerQuery(
            sellerId: Ulid::fromString($id),
            tenantId: $this->activeTenantProvider->getActiveTenantId(),
            actorUserId: $user->getId(),
        ));

        return $this->render('seller/show.html.twig', [
            'seller' => $seller,
        ]);
    }
}
