<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\Entity\AuthUser;
use GardenManager\CustomAttribute\Application\Query\ListDefinitionsQuery;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use GardenManager\Shared\Infrastructure\Bus\QueryDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ListDefinitionsAction extends AbstractController
{
    public function __construct(
        private readonly QueryDispatcher $queryDispatcher,
        private readonly ActiveTenantProviderInterface $activeTenantProvider,
    ) {
    }

    #[Route(
        path: '/settings/custom-attributes',
        name: 'custom_attribute_index',
        methods: ['GET'],
    )]
    public function __invoke(Request $request): Response
    {
        /** @var AuthUser $user */
        $user = $this->getUser();

        $tenantId = $this->activeTenantProvider->getActiveTenantId();
        $page = $request->query->getInt('page', 1);
        $entityType = $request->query->get('entityType');

        $pager = $this->queryDispatcher->query(new ListDefinitionsQuery(
            actorUserId: $user->getId(),
            tenantId: $tenantId,
            entityType: $entityType,
            page: $page,
        ));

        return $this->render('custom_attribute/index.html.twig', [
            'pager' => $pager,
            'currentEntityType' => $entityType,
        ]);
    }
}
