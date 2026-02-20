<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use GardenManager\Shared\Infrastructure\Bus\CommandDispatcher;
use GardenManager\Tenant\Application\Command\InviteMemberCommand;
use GardenManager\Tenant\Application\Dto\InviteMemberDto;
use GardenManager\Tenant\Domain\Enum\TenantMembershipRole;
use GardenManager\Tenant\Domain\Exception\TenantException;
use GardenManager\Tenant\Infrastructure\Form\InviteMemberFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;

#[IsGranted('ROLE_USER')]
final class InviteMemberAction extends AbstractController
{
    public function __construct(
        private readonly CommandDispatcher $commandDispatcher,
        private readonly ActiveTenantProviderInterface $activeTenantProvider,
    ) {
    }

    #[Route(
        path: '/tenant/members/invite',
        name: 'tenant_invite_member',
        methods: ['GET', 'POST'],
    )]
    public function __invoke(Request $request): Response
    {
        $dto = new InviteMemberDto();
        $form = $this->createForm(InviteMemberFormType::class, $dto);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var AuthUser $user */
            $user = $this->getUser();

            try {
                $this->commandDispatcher->dispatchCommand(new InviteMemberCommand(
                    membershipId: new Ulid(),
                    tenantId: $this->activeTenantProvider->getActiveTenantId(),
                    inviteeEmail: $dto->email ?? '',
                    role: $dto->role ?? TenantMembershipRole::MEMBER,
                    actorUserId: $user->getId(),
                ));

                $this->addFlash('success', 'Member invited successfully.');

                return $this->redirectToRoute('tenant_members');
            } catch (TenantException $e) {
                $form->get('email')->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('tenant/invite.html.twig', [
            'form' => $form,
        ]);
    }
}
