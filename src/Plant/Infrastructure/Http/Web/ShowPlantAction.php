<?php

declare(strict_types=1);

namespace GardenManager\Plant\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Plant\Application\Query\GetPlantQuery;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use GardenManager\Shared\Infrastructure\Bus\QueryDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Ulid;

final class ShowPlantAction extends AbstractController
{
    public function __construct(
        private readonly QueryDispatcher $queryDispatcher,
        private readonly ActiveTenantProviderInterface $activeTenantProvider,
    ) {
    }

    #[Route(
        path: '/plants/{plantId}',
        name: 'plant_show',
        methods: ['GET'],
    )]
    public function __invoke(Ulid $plantId): Response
    {
        /** @var AuthUser $user */
        $user = $this->getUser();

        $plantView = $this->queryDispatcher->query(new GetPlantQuery(
            $plantId,
            $this->activeTenantProvider->getActiveTenantId(),
            $user->getId(),
        ));

        return $this->render('plant/show.html.twig', [
            'plant' => $plantView,
        ]);
    }
}
