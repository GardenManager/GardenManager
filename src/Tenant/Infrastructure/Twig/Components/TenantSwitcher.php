<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Infrastructure\Twig\Components;

use GardenManager\Auth\Domain\Entity\AuthUser;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use GardenManager\Shared\Infrastructure\Bus\QueryDispatcher;
use GardenManager\Tenant\Application\Query\ListUserTenantsQuery;
use GardenManager\Tenant\Application\View\UserTenantView;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Uid\Ulid;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class TenantSwitcher
{
    /** @var list<UserTenantView>|null */
    private ?array $cachedTenants = null;

    public function __construct(
        private readonly QueryDispatcher $queryDispatcher,
        private readonly ActiveTenantProviderInterface $activeTenantProvider,
        private readonly Security $security,
    ) {
    }

    /** @return list<UserTenantView> */
    public function getTenants(): array
    {
        if ($this->cachedTenants !== null) {
            return $this->cachedTenants;
        }

        /** @var AuthUser|null $user */
        $user = $this->security->getUser();

        if ($user === null) {
            return $this->cachedTenants = [];
        }

        return $this->cachedTenants = $this->queryDispatcher->query(
            new ListUserTenantsQuery($user->getId()),
        );
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

        foreach ($this->getTenants() as $tenant) {
            if ($tenant->tenantId->equals($activeTenantId)) {
                return $tenant->tenantName;
            }
        }

        return '';
    }
}
