<?php

namespace GardenManager\Seller\Infrastructure\Http\Api;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Seller\Application\Command\DeleteSellerCommand;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;

#[IsGranted('ROLE_USER')]
final class DeleteSellerApiAction extends AbstractController
{
    public function __construct(
        private readonly CommandDispatcher $commandDispatcher,
    ) {
    }

    #[Route('/api/sellers/{id}', name: 'api_seller_delete', methods: ['DELETE'], requirements: ['id' => '[0-9A-Z]{26}'])]
    public function __invoke(string $id): JsonResponse
    {
        /** @var AuthUser $user */
        $user = $this->getUser();

        $this->commandDispatcher->dispatchCommand(new DeleteSellerCommand(
            sellerId: Ulid::fromString($id),
            ownerId: $user->getId(),
        ));

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
