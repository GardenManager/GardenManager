<?php

namespace GardenManager\Plant\Infrastructure\Http\Web;

use GardenManager\Plant\Application\Command\DeletePlantCommand;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;

#[IsGranted("ROLE_USER")]
final class DeletePlantAction extends AbstractController
{
    public function __construct(
        private readonly CommandDispatcher $commandDispatcher
    )
    {
    }

    #[Route(
        path: '/plants/{plantId}/delete',
        name: 'plant_delete',
        methods: ['POST']
    )]
    public function __invoke(Ulid $plantId): Response
    {
        /** @var Ulid $userId */
        $userId = $this->getUser()->getId();

        $this->commandDispatcher->dispatchCommand(new DeletePlantCommand($plantId, $userId));
        $this->addFlash('success', 'Seller deleted successfully.');

        return $this->redirectToRoute('plant_index');
    }
}
