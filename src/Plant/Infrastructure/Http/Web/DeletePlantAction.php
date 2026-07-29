<?php

declare(strict_types=1);

namespace GardenManager\Plant\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\Entity\AuthUser;
use GardenManager\Plant\Application\Command\DeletePlantCommand;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Ulid;

final class DeletePlantAction extends AbstractController
{
    public function __construct(
        private readonly CommandDispatcher $commandDispatcher,
        private readonly ActiveTenantProviderInterface $activeTenantProvider,
    ) {
    }

    #[Route(
        path: '/plants/{plantId}/delete',
        name: 'plant_delete',
        methods: ['POST'],
    )]
    public function __invoke(Ulid $plantId): RedirectResponse
    {
        /** @var AuthUser $user */
        $user = $this->getUser();

        $this->commandDispatcher->dispatchCommand(new DeletePlantCommand(
            plantId: $plantId,
            tenantId: $this->activeTenantProvider->getActiveTenantId(),
            actorUserId: $user->getId(),
        ));
        $this->addFlash('success', 'Plant deleted successfully.');

        return $this->redirectToRoute('plant_index');
    }
}
