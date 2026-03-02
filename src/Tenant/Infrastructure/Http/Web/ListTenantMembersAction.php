<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use GardenManager\Shared\Infrastructure\Bus\QueryDispatcher;
use GardenManager\Tenant\Application\Query\ListTenantMembersQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ListTenantMembersAction extends AbstractController
{
    public function __construct(
        private readonly QueryDispatcher $queryDispatcher,
        private readonly ActiveTenantProviderInterface $activeTenantProvider,
    ) {
    }

    #[Route(
        path: '/tenant/members',
        name: 'tenant_members',
        methods: ['GET'],
    )]
    public function __invoke(): Response
    {
        /** @var AuthUser $user */
        $user = $this->getUser();

        $members = $this->queryDispatcher->query(new ListTenantMembersQuery(
            tenantId: $this->activeTenantProvider->getActiveTenantId(),
            actorUserId: $user->getId(),
        ));

        return $this->render('tenant/members.html.twig', [
            'members' => $members,
        ]);
    }
}
