<?php

declare(strict_types=1);

namespace GardenManager\Seller\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Seller\Application\Command\UpdateSellerCommand;
use GardenManager\Seller\Application\Dto\UpdateSellerDto;
use GardenManager\Seller\Application\Query\GetSellerQuery;
use GardenManager\Seller\Application\Query\SellerDetailView;
use GardenManager\Seller\Infrastructure\Form\SellerFormType;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use GardenManager\Shared\Infrastructure\Bus\QueryDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;

#[IsGranted('ROLE_USER')]
final class EditSellerAction extends AbstractController
{
    public function __construct(
        private readonly CommandDispatcher $commandDispatcher,
        private readonly QueryDispatcher $queryDispatcher,
    ) {
    }

    #[Route('/sellers/{id}/edit', name: 'seller_edit', methods: ['GET', 'POST'], requirements: ['id' => '[0-9A-Z]{26}'])]
    public function __invoke(Request $request, string $id): Response
    {
        /** @var AuthUser $user */
        $user = $this->getUser();

        /** @var SellerDetailView $view */
        $view = $this->queryDispatcher->query(new GetSellerQuery(
            sellerId: Ulid::fromString($id),
            ownerId: $user->getId(),
        ));

        $dto = UpdateSellerDto::fromView($view);
        $form = $this->createForm(
            SellerFormType::class,
            $dto,
            [
                'data_class' => UpdateSellerDto::class,
            ],
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = new UpdateSellerCommand(
                sellerId: Ulid::fromString($id),
                ownerId: $user->getId(),
                name: $dto->name,
                email: $dto->email,
                phone: $dto->phone,
                description: $dto->description,
                address: $dto->address,
            );

            $this->commandDispatcher->dispatchCommand($command);
            $this->addFlash('success', 'Seller updated successfully.');

            return $this->redirectToRoute('seller_show', ['id' => $id]);
        }

        return $this->render('seller/edit.html.twig', [
            'form' => $form,
            'seller' => $view,
        ]);
    }
}
