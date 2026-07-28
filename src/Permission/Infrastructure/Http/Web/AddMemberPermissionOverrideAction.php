<?php

declare(strict_types=1);

namespace GardenManager\Permission\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\Entity\AuthUser;
use GardenManager\Permission\Application\Command\AddMemberPermissionOverrideCommand;
use GardenManager\Permission\Application\Dto\MemberPermissionOverrideFormDto;
use GardenManager\Permission\Application\Service\PermissionRegistryService;
use GardenManager\Permission\Domain\ValueObject\PermissionEntryParser;
use GardenManager\Permission\Infrastructure\Form\MemberPermissionOverrideFormType;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Ulid;

final class AddMemberPermissionOverrideAction extends AbstractController
{
    public function __construct(
        private readonly CommandDispatcher $commandDispatcher,
        private readonly ActiveTenantProviderInterface $activeTenantProvider,
        private readonly PermissionRegistryService $permissionRegistry,
    ) {
    }

    #[Route(
        path: '/tenant/members/{userId}/overrides',
        name: 'member_add_override',
        methods: ['POST'],
    )]
    public function __invoke(Request $request, Ulid $userId): RedirectResponse
    {
        $dto = new MemberPermissionOverrideFormDto();
        $form = $this->createForm(MemberPermissionOverrideFormType::class, $dto, [
            'permission_choices' => $this->permissionRegistry->getChoices(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var AuthUser $user */
            $user = $this->getUser();

            $prefixedPermission = PermissionEntryParser::format(
                $dto->permission,
                $dto->granted,
            );

            $this->commandDispatcher->dispatchCommand(new AddMemberPermissionOverrideCommand(
                tenantId: $this->activeTenantProvider->getActiveTenantId(),
                userId: $userId,
                prefixedPermission: $prefixedPermission,
                actorUserId: $user->getId(),
            ));

            $this->addFlash('success', 'Permission override added successfully.');
        }

        return $this->redirectToRoute('member_permissions', ['userId' => $userId]);
    }
}
