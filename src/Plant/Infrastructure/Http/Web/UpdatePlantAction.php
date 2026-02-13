<?php

declare(strict_types=1);

namespace GardenManager\Plant\Infrastructure\Http\Web;

use GardenManager\Plant\Application\Command\UpdatePlantCommand;
use GardenManager\Plant\Application\Dto\PlantFormDto;
use GardenManager\Plant\Application\Query\GetPlantQuery;
use GardenManager\Plant\Application\Query\PlantDetailView;
use GardenManager\Plant\Infrastructure\Form\PlantFormType;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use GardenManager\Shared\Infrastructure\Bus\QueryDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;

#[IsGranted('ROLE_USER')]
final class UpdatePlantAction extends AbstractController
{
    public function __construct(
        private readonly CommandDispatcher $commandDispatcher,
        private readonly QueryDispatcher $queryDispatcher,
    ) {
    }

    #[Route(
        path: '/plants/{plantId}/update',
        name: 'plant_update',
        methods: ['GET', 'POST'],
    )]
    public function __invoke(Request $request, Ulid $plantId): Response
    {
        /** @var Ulid $userId */
        $userId = $this->getUser()->getId();

        /** @var PlantDetailView $plantView */
        $plantView = $this->queryDispatcher->query(new GetPlantQuery($plantId, $userId));
        $dto = PlantFormDto::fromView($plantView);
        $form = $this->createForm(PlantFormType::class, $dto);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = new UpdatePlantCommand(
                plantId: $plantId,
                ownerId: $userId,
                localName: $dto->localName,
                isHybrid: $dto->isHybrid,
                lifecycle: $dto->lifecycle,
                genus: $dto->genus,
                epithet: $dto->epithet,
                cultivar: $dto->cultivar,
            );

            $this->commandDispatcher->dispatchCommand($command);
            $this->addFlash('success', 'Plant updated successfully.');

            return $this->redirectToRoute('plant_show', ['plantId' => $plantId]);
        }

        return $this->render('plant/edit.html.twig', [
            'form' => $form,
            'plant' => $plantView,
        ]);
    }
}
