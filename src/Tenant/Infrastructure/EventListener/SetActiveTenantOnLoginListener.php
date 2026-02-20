<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Infrastructure\EventListener;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Shared\Infrastructure\Security\SessionActiveTenantProvider;
use GardenManager\Tenant\Domain\TenantMembershipRepositoryInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

#[AsEventListener(event: LoginSuccessEvent::class)]
final readonly class SetActiveTenantOnLoginListener
{
    public function __construct(
        private TenantMembershipRepositoryInterface $membershipRepository,
        private SessionActiveTenantProvider $activeTenantProvider,
    ) {
    }

    public function __invoke(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof AuthUser) {
            return;
        }

        $memberships = $this->membershipRepository->findByUserId($user->getId());

        if ($memberships === []) {
            return;
        }

        $this->activeTenantProvider->setActiveTenantId(
            $memberships[0]->getTenant()->getId(),
        );
    }
}
