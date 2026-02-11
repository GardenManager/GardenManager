<?php

declare(strict_types=1);

namespace GardenManager\Seller\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Seller\Application\Command\DeleteSellerCommand;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;

#[IsGranted('ROLE_USER')]
final class DeleteSellerAction extends AbstractController
{
    public function __construct(
        private readonly CommandDispatcher $commandDispatcher,
    ) {
    }

    #[Route('/sellers/{id}/delete', name: 'seller_delete', methods: ['POST'], requirements: ['id' => '[0-9A-Z]{26}'])]
    public function __invoke(Request $request, string $id): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete-seller-' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        /** @var AuthUser $user */
        $user = $this->getUser();

        $this->commandDispatcher->dispatchCommand(new DeleteSellerCommand(
            sellerId: Ulid::fromString($id),
            ownerId: $user->getId(),
        ));

        $this->addFlash('success', 'Seller deleted successfully.');

        return $this->redirectToRoute('seller_index');
    }
}
