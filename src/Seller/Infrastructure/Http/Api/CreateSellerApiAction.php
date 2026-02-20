<?php

declare(strict_types=1);

namespace GardenManager\Seller\Infrastructure\Http\Api;

use GardenManager\Seller\Application\Command\CreateSellerCommand;
use GardenManager\Seller\Application\Dto\Api\SellerApiRequest;
use GardenManager\Seller\Application\Dto\Api\SellerApiResponse;
use GardenManager\Seller\Application\Query\GetSellerQuery;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use GardenManager\Shared\Infrastructure\Bus\QueryDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;

#[IsGranted('ROLE_USER')]
final class CreateSellerApiAction extends AbstractController
{
    public function __construct(
        private readonly CommandDispatcher $commandDispatcher,
        private readonly QueryDispatcher $queryDispatcher,
        private readonly ActiveTenantProviderInterface $activeTenantProvider,
    ) {
    }

    #[Route('/api/sellers', name: 'api_seller_create', methods: ['POST'])]
    public function __invoke(#[MapRequestPayload] SellerApiRequest $apiRequest): JsonResponse
    {
        $sellerId = new Ulid();

        $command = new CreateSellerCommand(
            sellerId: $sellerId,
            tenantId: $this->activeTenantProvider->getActiveTenantId(),
            name: $apiRequest->name,
            email: $apiRequest->email,
            phone: $apiRequest->phone,
            description: $apiRequest->description,
            address: $apiRequest->address?->toAddressData(),
        );

        $this->commandDispatcher->dispatchCommand($command);

        $view = $this->queryDispatcher->query(new GetSellerQuery(
            sellerId: $sellerId,
            tenantId: $this->activeTenantProvider->getActiveTenantId(),
        ));

        return $this->json(
            SellerApiResponse::fromView($view),
            Response::HTTP_CREATED,
        );
    }
}
