<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\CustomAttribute\Application\Command\CreateDefinitionCommand;
use GardenManager\CustomAttribute\Application\Dto\DefinitionFormDto;
use GardenManager\CustomAttribute\Infrastructure\Form\DefinitionFormType;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Ulid;

final class CreateDefinitionAction extends AbstractController
{
    public function __construct(
        private readonly CommandDispatcher $commandDispatcher,
        private readonly ActiveTenantProviderInterface $activeTenantProvider,
    ) {
    }

    #[Route(
        path: '/settings/custom-attributes/new',
        name: 'custom_attribute_new',
        methods: ['GET', 'POST']
    )]
    public function __invoke(Request $request): Response
    {
        /** @var AuthUser $user */
        $user = $this->getUser();

        $dto = new DefinitionFormDto();
        $form = $this->createForm(DefinitionFormType::class, $dto, [
            'submit_label' => 'Create Attribute',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = new CreateDefinitionCommand(
                definitionId: new Ulid(),
                tenantId: $this->activeTenantProvider->getActiveTenantId(),
                actorUserId: $user->getId(),
                name: $dto->name,
                label: $dto->label,
                entityType: $dto->entityType,
                type: $dto->type,
                required: $dto->required ?? false,
                sortOrder: $dto->sortOrder ?? 0,
                options: $dto->getOptionsArray(),
            );

            $this->commandDispatcher->dispatchCommand($command);
            $this->addFlash('success', 'Custom attribute created successfully.');

            return $this->redirectToRoute('custom_attribute_index');
        }

        return $this->render('custom_attribute/new.html.twig', [
            'form' => $form,
        ]);
    }
}
