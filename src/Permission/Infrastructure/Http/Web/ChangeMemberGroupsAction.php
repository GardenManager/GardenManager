<?php

declare(strict_types=1);

namespace GardenManager\Permission\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Permission\Application\Command\ChangeMemberGroupsCommand;
use GardenManager\Permission\Infrastructure\Form\ChangeMemberGroupsFormType;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use GardenManager\Tenant\Domain\TenantRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;

final class ChangeMemberGroupsAction extends AbstractController
{
    public function __construct(
        private readonly CommandDispatcher $commandDispatcher,
        private readonly ActiveTenantProviderInterface $activeTenantProvider,
        private readonly TenantRepositoryInterface $tenantRepository,
    ) {
    }

    #[Route(
        path: '/tenant/members/{userId}/groups',
        name: 'member_change_groups',
        methods: ['POST'],
    )]
    public function __invoke(Request $request, Ulid $userId): RedirectResponse
    {
        $tenantId = $this->activeTenantProvider->getActiveTenantId();
        $tenant = $this->tenantRepository->getById($tenantId);
        $config = $tenant->getPermissionsConfig();

        $form = $this->createForm(ChangeMemberGroupsFormType::class, null, [
            'group_choices' => $config->getGroupChoices(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var AuthUser $user */
            $user = $this->getUser();

            $this->commandDispatcher->dispatchCommand(new ChangeMemberGroupsCommand(
                tenantId: $tenantId,
                userId: $userId,
                groupSlugs: $form->get('groupSlugs')->getData(),
                actorUserId: $user->getId(),
            ));

            $this->addFlash('success', 'Member groups updated successfully.');
        }

        return $this->redirectToRoute('member_permissions', ['userId' => $userId]);
    }
}
