<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Infrastructure\Http\Web;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Shared\Infrastructure\Security\SessionActiveTenantProvider;
use GardenManager\Tenant\Domain\TenantMembershipRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Ulid;

#[IsGranted('ROLE_USER')]
final class SwitchTenantAction extends AbstractController
{
    public function __construct(
        private readonly SessionActiveTenantProvider $activeTenantProvider,
        private readonly TenantMembershipRepositoryInterface $membershipRepository,
    ) {
    }

    #[Route('/tenant/switch', name: 'tenant_switch', methods: ['POST'])]
    public function __invoke(Request $request): RedirectResponse
    {
        /** @var AuthUser $user */
        $user = $this->getUser();
        $tenantId = Ulid::fromString($request->request->getString('tenant_id'));

        $membership = $this->membershipRepository->findByTenantIdAndUserId($tenantId, $user->getId());

        if ($membership === null) {
            throw $this->createAccessDeniedException('You are not a member of this tenant.');
        }

        $this->activeTenantProvider->setActiveTenantId($tenantId);

        return $this->redirectToRoute('app_dashboard');
    }
}
