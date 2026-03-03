<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use GardenManager\Tenant\Application\Command\RemoveMemberCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Ulid;

final class RemoveMemberAction extends AbstractController
{
    public function __construct(
        private readonly CommandDispatcher $commandDispatcher,
        private readonly ActiveTenantProviderInterface $activeTenantProvider,
    ) {
    }

    #[Route(
        path: '/tenant/members/{userId}/remove',
        name: 'tenant_remove_member',
        methods: ['POST'])]
    public function __invoke(Ulid $userId): RedirectResponse
    {
        /** @var AuthUser $user */
        $user = $this->getUser();

        $this->commandDispatcher->dispatchCommand(new RemoveMemberCommand(
            tenantId: $this->activeTenantProvider->getActiveTenantId(),
            memberUserId: $userId,
            actorUserId: $user->getId(),
        ));

        $this->addFlash('success', 'Member removed successfully.');

        return $this->redirectToRoute('tenant_members');
    }
}
