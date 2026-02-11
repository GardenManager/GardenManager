<?php

namespace GardenManager\Seller\Infrastructure\Http\Api;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Seller\Application\Command\UpdateSellerCommand;
use GardenManager\Seller\Application\Dto\Api\SellerApiRequest;
use GardenManager\Seller\Application\Dto\Api\SellerApiResponse;
use GardenManager\Seller\Application\Query\GetSellerQuery;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use GardenManager\Shared\Infrastructure\Bus\QueryDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;

#[IsGranted('ROLE_USER')]
final class UpdateSellerApiAction extends AbstractController
{
    public function __construct(
        private readonly CommandDispatcher $commandDispatcher,
        private readonly QueryDispatcher $queryDispatcher,
    ) {
    }

    #[Route('/api/sellers/{id}', name: 'api_seller_update', methods: ['PUT'], requirements: ['id' => '[0-9A-Z]{26}'])]
    public function __invoke(#[MapRequestPayload] SellerApiRequest $apiRequest, string $id): JsonResponse
    {
        /** @var AuthUser $user */
        $user = $this->getUser();

        $command = new UpdateSellerCommand(
            sellerId: Ulid::fromString($id),
            ownerId: $user->getId(),
            name: $apiRequest->name,
            email: $apiRequest->email,
            phone: $apiRequest->phone,
            description: $apiRequest->description,
            address: $apiRequest->address?->toAddressData(),
        );

        $this->commandDispatcher->dispatchCommand($command);

        $view = $this->queryDispatcher->query(new GetSellerQuery(
            sellerId: Ulid::fromString($id),
            ownerId: $user->getId(),
        ));

        return $this->json(SellerApiResponse::fromView($view));
    }
}
