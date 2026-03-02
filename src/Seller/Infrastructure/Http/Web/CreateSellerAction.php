<?php

declare(strict_types=1);

namespace GardenManager\Seller\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Seller\Application\Command\CreateSellerCommand;
use GardenManager\Seller\Application\Dto\SellerFormDto;
use GardenManager\Seller\Infrastructure\Form\SellerFormType;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Ulid;

final class CreateSellerAction extends AbstractController
{
    public function __construct(
        private readonly CommandDispatcher $commandDispatcher,
        private readonly ActiveTenantProviderInterface $activeTenantProvider,
    ) {
    }

    #[Route('/sellers/new', name: 'seller_new', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $dto = new SellerFormDto();
        $form = $this->createForm(SellerFormType::class, $dto, [
            'submit_label' => 'Create Seller',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sellerId = new Ulid();

            /** @var AuthUser $user */
            $user = $this->getUser();

            $command = new CreateSellerCommand(
                sellerId: $sellerId,
                tenantId: $this->activeTenantProvider->getActiveTenantId(),
                actorUserId: $user->getId(),
                name: $dto->name ?? '',
                email: $dto->email ?? '',
                phone: $dto->phone,
                description: $dto->description,
                address: $dto->address,
            );

            $this->commandDispatcher->dispatchCommand($command);
            $this->addFlash('success', 'Seller created successfully.');

            return $this->redirectToRoute('seller_show', ['id' => $sellerId]);
        }

        return $this->render('seller/new.html.twig', [
            'form' => $form,
        ]);
    }
}
