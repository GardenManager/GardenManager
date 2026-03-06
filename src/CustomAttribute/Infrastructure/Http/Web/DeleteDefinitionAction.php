<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\CustomAttribute\Application\Command\DeleteDefinitionCommand;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Ulid;

final class DeleteDefinitionAction extends AbstractController
{
    public function __construct(
        private readonly CommandDispatcher $commandDispatcher,
        private readonly ActiveTenantProviderInterface $activeTenantProvider,
    ) {
    }

    #[Route(
        path: '/settings/custom-attributes/{id}/delete',
        name: 'custom_attribute_delete',
        methods: ['POST'],
    )]
    public function __invoke(Ulid $id): RedirectResponse
    {
        /** @var AuthUser $user */
        $user = $this->getUser();

        $this->commandDispatcher->dispatchCommand(new DeleteDefinitionCommand(
            definitionId: $id,
            tenantId: $this->activeTenantProvider->getActiveTenantId(),
            actorUserId: $user->getId(),
        ));

        $this->addFlash('success', 'Custom attribute deleted successfully.');

        return $this->redirectToRoute('custom_attribute_index');
    }
}
