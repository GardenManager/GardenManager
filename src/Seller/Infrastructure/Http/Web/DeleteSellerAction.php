<?php

declare(strict_types=1);

namespace GardenManager\Seller\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\Entity\AuthUser;
use GardenManager\Seller\Application\Command\DeleteSellerCommand;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Ulid;

final class DeleteSellerAction extends AbstractController
{
    public function __construct(
        private readonly CommandDispatcher $commandDispatcher,
        private readonly ActiveTenantProviderInterface $activeTenantProvider,
    ) {
    }

    #[Route('/sellers/{id}/delete', name: 'seller_delete', methods: ['POST'], requirements: ['id' => '[0-9A-Z]{26}'])]
    public function __invoke(string $id): RedirectResponse
    {
        /** @var AuthUser $user */
        $user = $this->getUser();

        $this->commandDispatcher->dispatchCommand(new DeleteSellerCommand(
            sellerId: Ulid::fromString($id),
            tenantId: $this->activeTenantProvider->getActiveTenantId(),
            actorUserId: $user->getId(),
        ));

        $this->addFlash('success', 'Seller deleted successfully.');

        return $this->redirectToRoute('seller_index');
    }
}
