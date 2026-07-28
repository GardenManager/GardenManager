<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\Entity\AuthUser;
use GardenManager\CustomAttribute\Application\Command\UpdateDefinitionCommand;
use GardenManager\CustomAttribute\Application\Dto\DefinitionFormDto;
use GardenManager\CustomAttribute\Application\Query\GetDefinitionQuery;
use GardenManager\CustomAttribute\Application\View\DefinitionDetailView;
use GardenManager\CustomAttribute\Infrastructure\Form\DefinitionFormType;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use GardenManager\Shared\Infrastructure\Bus\QueryDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Ulid;

final class EditDefinitionAction extends AbstractController
{
    public function __construct(
        private readonly CommandDispatcher $commandDispatcher,
        private readonly QueryDispatcher $queryDispatcher,
        private readonly ActiveTenantProviderInterface $activeTenantProvider,
    ) {
    }

    #[Route(
        path: '/settings/custom-attributes/{id}/edit',
        name: 'custom_attribute_edit',
        methods: ['GET', 'POST'],
    )]
    public function __invoke(Request $request, Ulid $id): Response
    {
        /** @var AuthUser $user */
        $user = $this->getUser();
        $tenantId = $this->activeTenantProvider->getActiveTenantId();

        /** @var DefinitionDetailView $definitionView */
        $definitionView = $this->queryDispatcher->query(new GetDefinitionQuery($id, $tenantId, $user->getId()));
        $dto = DefinitionFormDto::fromView($definitionView);

        $form = $this->createForm(DefinitionFormType::class, $dto, [
            'submit_label' => 'Save Changes',
            'is_edit' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            \assert($dto->label !== null);

            $command = new UpdateDefinitionCommand(
                definitionId: $id,
                tenantId: $tenantId,
                actorUserId: $user->getId(),
                label: $dto->label,
                required: $dto->required ?? false,
                sortOrder: $dto->sortOrder ?? 0,
                options: $dto->getOptionsArray(),
            );

            $this->commandDispatcher->dispatchCommand($command);
            $this->addFlash('success', 'Custom attribute updated successfully.');

            return $this->redirectToRoute('custom_attribute_index');
        }

        return $this->render('custom_attribute/edit.html.twig', [
            'form' => $form,
            'definition' => $definitionView,
        ]);
    }
}
