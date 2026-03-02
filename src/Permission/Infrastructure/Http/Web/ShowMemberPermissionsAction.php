<?php

declare(strict_types=1);

namespace GardenManager\Permission\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Permission\Application\Dto\MemberPermissionOverrideFormDto;
use GardenManager\Permission\Application\Query\GetMemberPermissionDetailQuery;
use GardenManager\Permission\Application\Service\PermissionRegistryService;
use GardenManager\Permission\Application\View\MemberPermissionView;
use GardenManager\Permission\Infrastructure\Form\ChangeMemberGroupsFormType;
use GardenManager\Permission\Infrastructure\Form\MemberPermissionOverrideFormType;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use GardenManager\Shared\Infrastructure\Bus\QueryDispatcher;
use GardenManager\Tenant\Domain\TenantRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;

final class ShowMemberPermissionsAction extends AbstractController
{
    public function __construct(
        private readonly QueryDispatcher $queryDispatcher,
        private readonly ActiveTenantProviderInterface $activeTenantProvider,
        private readonly TenantRepositoryInterface $tenantRepository,
        private readonly PermissionRegistryService $permissionRegistry,
    ) {
    }

    #[Route(
        path: '/tenant/members/{userId}/permissions',
        name: 'member_permissions',
        methods: ['GET'],
    )]
    public function __invoke(Ulid $userId): Response
    {
        /** @var AuthUser $user */
        $user = $this->getUser();
        $tenantId = $this->activeTenantProvider->getActiveTenantId();

        /** @var MemberPermissionView $member */
        $member = $this->queryDispatcher->query(new GetMemberPermissionDetailQuery(
            userId: $userId,
            tenantId: $tenantId,
            actorUserId: $user->getId(),
        ));

        $tenant = $this->tenantRepository->getById($tenantId);
        $config = $tenant->getPermissionsConfig();

        $groupForm = $this->createForm(ChangeMemberGroupsFormType::class, [
            'groupSlugs' => $member->groupSlugs,
        ], [
            'group_choices' => $config->getGroupChoices(),
            'action' => $this->generateUrl('member_change_groups', ['userId' => $userId]),
        ]);

        $overrideForm = $this->createForm(MemberPermissionOverrideFormType::class, new MemberPermissionOverrideFormDto(), [
            'permission_choices' => $this->permissionRegistry->getChoices(),
            'action' => $this->generateUrl('member_add_override', ['userId' => $userId]),
        ]);

        return $this->render('permission/member_detail.html.twig', [
            'member' => $member,
            'groupForm' => $groupForm,
            'overrideForm' => $overrideForm,
        ]);
    }
}
