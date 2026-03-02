<?php

declare(strict_types=1);

namespace GardenManager\Permission\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Permission\Application\Command\RemoveMemberPermissionOverrideCommand;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;

final class RemoveMemberPermissionOverrideAction extends AbstractController
{
    public function __construct(
        private readonly CommandDispatcher $commandDispatcher,
        private readonly ActiveTenantProviderInterface $activeTenantProvider,
    ) {
    }

    #[Route(
        path: '/tenant/members/{userId}/overrides/{permission}/remove',
        name: 'member_remove_override',
        methods: ['POST'],
    )]
    public function __invoke(Ulid $userId, string $permission): RedirectResponse
    {
        /** @var AuthUser $user */
        $user = $this->getUser();

        $this->commandDispatcher->dispatchCommand(new RemoveMemberPermissionOverrideCommand(
            userId: $userId,
            permission: $permission,
            tenantId: $this->activeTenantProvider->getActiveTenantId(),
            actorUserId: $user->getId(),
        ));

        $this->addFlash('success', 'Permission override removed successfully.');

        return $this->redirectToRoute('member_permissions', ['userId' => $userId]);
    }
}
