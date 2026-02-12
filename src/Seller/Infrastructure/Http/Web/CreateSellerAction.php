<?php

declare(strict_types=1);

namespace GardenManager\Seller\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Seller\Application\Command\CreateSellerCommand;
use GardenManager\Seller\Application\Dto\SellerFormDto;
use GardenManager\Seller\Infrastructure\Form\SellerFormType;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;

#[IsGranted('ROLE_USER')]
final class CreateSellerAction extends AbstractController
{
    public function __construct(
        private readonly CommandDispatcher $commandDispatcher,
    ) {
    }

    #[Route('/sellers/new', name: 'seller_new', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $dto = new SellerFormDto();
        $form = $this->createForm(SellerFormType::class, $dto);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var AuthUser $user */
            $user = $this->getUser();
            $sellerId = new Ulid();

            $command = new CreateSellerCommand(
                sellerId: $sellerId,
                ownerId: $user->getId(),
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
