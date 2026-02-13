<?php

namespace GardenManager\Plant\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Plant\Application\Command\CreatePlantCommand;
use GardenManager\Plant\Application\Dto\PlantFormDto;
use GardenManager\Plant\Infrastructure\Form\PlantFormType;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;

#[IsGranted('ROLE_USER')]
class CreatePlantAction extends AbstractController
{
    public function __construct(
        private readonly CommandDispatcher $commandDispatcher,
    ) {}

    #[Route(path: '/plants/new', name: 'plant_new', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $dto = new PlantFormDto();
        $form = $this->createForm(PlantFormType::class, $dto);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var AuthUser $user */
            $user = $this->getUser();

            $command = new CreatePlantCommand(
                plantId: new Ulid(),
                ownerId: $user->getId(),
                localName: $dto->localName ?? '',
                isHybrid: $dto->isHybrid,
                lifecycle: $dto->lifecycle,
                genus: $dto->genus,
                epithet: $dto->epithet,
                cultivar: $dto->cultivar,
            );

            $this->commandDispatcher->dispatchCommand($command);
            $this->addFlash('success', 'Plant created successfully.');

            return $this->redirectToRoute('plant_show', ['plantId' => $command->plantId]);
        }

        return $this->render('plant/new.html.twig', [
            'form' => $form,
        ]);
    }
}
