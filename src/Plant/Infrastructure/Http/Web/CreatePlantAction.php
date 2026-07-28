<?php

declare(strict_types=1);

namespace GardenManager\Plant\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\Entity\AuthUser;
use GardenManager\CustomAttribute\Application\Command\SetAttributeValuesCommand;
use GardenManager\Plant\Application\Command\CreatePlantCommand;
use GardenManager\Plant\Application\Dto\PlantFormDto;
use GardenManager\Plant\Infrastructure\Form\PlantFormType;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Ulid;

final class CreatePlantAction extends AbstractController
{
    public function __construct(
        private readonly CommandDispatcher $commandDispatcher,
        private readonly ActiveTenantProviderInterface $activeTenantProvider,
    ) {
    }

    #[Route(path: '/plants/new', name: 'plant_new', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        /** @var AuthUser $user */
        $user = $this->getUser();

        $dto = new PlantFormDto();
        $form = $this->createForm(PlantFormType::class, $dto, [
            'submit_label' => 'Create Plant',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tenantId = $this->activeTenantProvider->getActiveTenantId();
            $plantId = new Ulid();

            $command = new CreatePlantCommand(
                plantId: $plantId,
                tenantId: $tenantId,
                actorUserId: $user->getId(),
                localName: $dto->localName ?? '',
                isHybrid: $dto->isHybrid,
                lifecycle: $dto->lifecycle,
                genus: $dto->genus,
                epithet: $dto->epithet,
                cultivar: $dto->cultivar,
            );

            $this->commandDispatcher->dispatchCommand($command);

            $customAttributesForm = $form->get('customAttributes');
            $values = [];
            foreach ($customAttributesForm->all() as $name => $field) {
                $values[$name] = $field->getData();
            }

            if ($values !== []) {
                $this->commandDispatcher->dispatchCommand(new SetAttributeValuesCommand(
                    tenantId: $tenantId,
                    actorUserId: $user->getId(),
                    entityType: 'plant',
                    entityId: $plantId,
                    values: $values,
                ));
            }

            $this->addFlash('success', 'Plant created successfully.');

            return $this->redirectToRoute('plant_show', ['plantId' => $plantId]);
        }

        return $this->render('plant/new.html.twig', [
            'form' => $form,
        ]);
    }
}
