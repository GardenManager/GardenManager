<?php

declare(strict_types=1);

namespace GardenManager\Plant\Infrastructure\Http\Web;

use GardenManager\Plant\Application\Command\DeletePlantCommand;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;

#[IsGranted('ROLE_USER')]
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
        $this->commandDispatcher->dispatchCommand(new DeletePlantCommand(
            $plantId,
            $this->activeTenantProvider->getActiveTenantId(),
        ));
        $this->addFlash('success', 'Plant deleted successfully.');

        return $this->redirectToRoute('plant_index');
    }
}
