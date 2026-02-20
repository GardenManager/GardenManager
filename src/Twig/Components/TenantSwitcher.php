<?php

declare(strict_types=1);

namespace GardenManager\Twig\Components;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Shared\Infrastructure\Security\SessionActiveTenantProvider;
use GardenManager\Tenant\Domain\TenantMembership;
use GardenManager\Tenant\Domain\TenantMembershipRepositoryInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Uid\Ulid;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class TenantSwitcher
{
    /** @var list<TenantMembership>|null */
    private ?array $cachedMemberships = null;

    public function __construct(
        private readonly TenantMembershipRepositoryInterface $membershipRepository,
        private readonly SessionActiveTenantProvider $activeTenantProvider,
        private readonly Security $security,
    ) {
    }

    /** @return list<TenantMembership> */
    public function getMemberships(): array
    {
        if ($this->cachedMemberships !== null) {
            return $this->cachedMemberships;
        }

        /** @var AuthUser|null $user */
        $user = $this->security->getUser();

        if ($user === null) {
            return $this->cachedMemberships = [];
        }

        return $this->cachedMemberships = $this->membershipRepository->findByUserId($user->getId());
    }

    public function getActiveTenantId(): ?Ulid
    {
        if (!$this->activeTenantProvider->hasActiveTenant()) {
            return null;
        }

        return $this->activeTenantProvider->getActiveTenantId();
    }

    public function getActiveTenantName(): string
    {
        $activeTenantId = $this->getActiveTenantId();

        if ($activeTenantId === null) {
            return '';
        }

        foreach ($this->getMemberships() as $membership) {
            if ($membership->getTenant()->getId()->equals($activeTenantId)) {
                return $membership->getTenant()->getName();
            }
        }

        return '';
    }
}
