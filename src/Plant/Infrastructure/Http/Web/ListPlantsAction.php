<?php

declare(strict_types=1);

namespace GardenManager\Plant\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Plant\Application\Query\ListPlantsQuery;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use GardenManager\Shared\Infrastructure\Bus\QueryDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ListPlantsAction extends AbstractController
{
    public function __construct(
        private readonly QueryDispatcher $queryDispatcher,
        private readonly ActiveTenantProviderInterface $activeTenantProvider,
    ) {
    }

    #[Route(path: '/plants', name: 'plant_index', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        /** @var AuthUser $user */
        $user = $this->getUser();
        $tenantId = $this->activeTenantProvider->getActiveTenantId();

        $pager = $this->queryDispatcher->query(
            new ListPlantsQuery(
                actorUserId: $user->getId(),
                tenantId: $tenantId,
                page: $request->query->getInt('page', 1),
            ),
        );

        return $this->render('plant/index.html.twig', [
            'pager' => $pager,
        ]);
    }
}
