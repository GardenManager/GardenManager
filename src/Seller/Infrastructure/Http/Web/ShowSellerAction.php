<?php

namespace GardenManager\Seller\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Seller\Application\Query\GetSellerQuery;
use GardenManager\Shared\Infrastructure\Bus\QueryDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;

#[IsGranted('ROLE_USER')]
final class ShowSellerAction extends AbstractController
{
    public function __construct(
        private readonly QueryDispatcher $queryDispatcher,
    ) {
    }

    #[Route('/sellers/{id}', name: 'seller_show', methods: ['GET'], requirements: ['id' => '[0-9A-Z]{26}'])]
    public function __invoke(string $id): Response
    {
        /** @var AuthUser $user */
        $user = $this->getUser();

        $seller = $this->queryDispatcher->query(new GetSellerQuery(
            sellerId: Ulid::fromString($id),
            ownerId: $user->getId(),
        ));

        return $this->render('seller/show.html.twig', [
            'seller' => $seller,
        ]);
    }
}
