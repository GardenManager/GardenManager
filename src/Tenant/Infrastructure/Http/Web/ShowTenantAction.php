<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\Entity\AuthUser;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use GardenManager\Shared\Infrastructure\Bus\QueryDispatcher;
use GardenManager\Tenant\Application\Query\GetTenantQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ShowTenantAction extends AbstractController
{
    public function __construct(
        private readonly QueryDispatcher $queryDispatcher,
        private readonly ActiveTenantProviderInterface $activeTenantProvider,
    ) {
    }

    #[Route(
        path: '/tenant',
        name: 'tenant_show',
        methods: ['GET'],
    )]
    public function __invoke(): Response
    {
        /** @var AuthUser $user */
        $user = $this->getUser();

        $tenant = $this->queryDispatcher->query(new GetTenantQuery(
            tenantId: $this->activeTenantProvider->getActiveTenantId(),
            actorUserId: $user->getId(),
        ));

        return $this->render('tenant/settings.html.twig', [
            'tenant' => $tenant,
        ]);
    }
}
