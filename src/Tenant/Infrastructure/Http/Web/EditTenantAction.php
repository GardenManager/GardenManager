<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use GardenManager\Shared\Infrastructure\Bus\QueryDispatcher;
use GardenManager\Tenant\Application\Command\UpdateTenantCommand;
use GardenManager\Tenant\Application\Dto\TenantFormDto;
use GardenManager\Tenant\Application\Query\GetTenantQuery;
use GardenManager\Tenant\Application\View\TenantDetailView;
use GardenManager\Tenant\Infrastructure\Form\TenantFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EditTenantAction extends AbstractController
{
    public function __construct(
        private readonly CommandDispatcher $commandDispatcher,
        private readonly QueryDispatcher $queryDispatcher,
        private readonly ActiveTenantProviderInterface $activeTenantProvider,
    ) {
    }

    #[Route(
        path: '/tenant/edit',
        name: 'tenant_edit',
        methods: ['GET', 'POST'],
    )]
    public function __invoke(Request $request): Response
    {
        /** @var AuthUser $user */
        $user = $this->getUser();
        $tenantId = $this->activeTenantProvider->getActiveTenantId();

        /** @var TenantDetailView $view */
        $view = $this->queryDispatcher->query(new GetTenantQuery(
            tenantId: $tenantId,
            actorUserId: $user->getId(),
        ));

        $dto = TenantFormDto::fromView($view);
        $form = $this->createForm(TenantFormType::class, $dto, [
            'submit_label' => 'Save Changes',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandDispatcher->dispatchCommand(new UpdateTenantCommand(
                tenantId: $tenantId,
                name: $dto->name ?? '',
                actorUserId: $user->getId(),
            ));

            $this->addFlash('success', 'Tenant updated successfully.');

            return $this->redirectToRoute('tenant_show');
        }

        return $this->render('tenant/edit.html.twig', [
            'form' => $form,
            'tenant' => $view,
        ]);
    }
}
