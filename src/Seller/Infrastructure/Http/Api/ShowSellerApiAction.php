<?php

declare(strict_types=1);

namespace GardenManager\Seller\Infrastructure\Http\Api;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Seller\Application\Dto\Api\SellerApiResponse;
use GardenManager\Seller\Application\Query\GetSellerQuery;
use GardenManager\Shared\Infrastructure\Bus\QueryDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;

#[IsGranted('ROLE_USER')]
final class ShowSellerApiAction extends AbstractController
{
    public function __construct(
        private readonly QueryDispatcher $queryDispatcher,
    ) {
    }

    #[Route('/api/sellers/{id}', name: 'api_seller_show', methods: ['GET'], requirements: ['id' => '[0-9A-Z]{26}'])]
    public function __invoke(string $id): JsonResponse
    {
        /** @var AuthUser $user */
        $user = $this->getUser();

        $view = $this->queryDispatcher->query(new GetSellerQuery(
            sellerId: Ulid::fromString($id),
            ownerId: $user->getId(),
        ));

        return $this->json(SellerApiResponse::fromView($view));
    }
}
