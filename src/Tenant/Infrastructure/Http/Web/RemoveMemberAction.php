<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use GardenManager\Shared\Infrastructure\Http\TurboStreamToastRenderer;
use GardenManager\Tenant\Application\Command\RemoveMemberCommand;
use GardenManager\Tenant\Domain\Exception\TenantException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;

#[IsGranted('ROLE_USER')]
final class RemoveMemberAction extends AbstractController
{
    public function __construct(
        private readonly CommandDispatcher $commandDispatcher,
        private readonly ActiveTenantProviderInterface $activeTenantProvider,
        private readonly TurboStreamToastRenderer $toastRenderer,
    ) {
    }

    #[Route(
        path: '/tenant/members/{userId}/remove',
        name: 'tenant_remove_member',
        methods: ['POST'])]
    public function __invoke(Ulid $userId): Response
    {
        /** @var AuthUser $user */
        $user = $this->getUser();

        try {
            $this->commandDispatcher->dispatchCommand(new RemoveMemberCommand(
                tenantId: $this->activeTenantProvider->getActiveTenantId(),
                memberUserId: $userId,
                actorUserId: $user->getId(),
            ));
        } catch (TenantException $e) {
            return $this->toastRenderer->createErrorResponse($e->getMessage());
        }

        $this->addFlash('success', 'Member removed successfully.');

        return $this->redirectToRoute('tenant_members');
    }
}
