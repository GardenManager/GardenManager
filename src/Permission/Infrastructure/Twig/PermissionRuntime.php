<?php

declare(strict_types=1);

namespace GardenManager\Permission\Infrastructure\Twig;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Permission\Domain\Service\PermissionResolverInterface;
use GardenManager\Permission\Infrastructure\Profiler\PermissionProfilerDataStore;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\RuntimeExtensionInterface;

final readonly class PermissionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private PermissionResolverInterface $permissionResolver,
        private ActiveTenantProviderInterface $activeTenantProvider,
        private Security $security,
        private ?PermissionProfilerDataStore $dataStore = null,
    ) {
    }

    public function hasPermission(string $permission): bool
    {
        $user = $this->security->getUser();

        if (!$user instanceof AuthUser) {
            return false;
        }

        if (!$this->activeTenantProvider->hasActiveTenant()) {
            return false;
        }

        $this->dataStore?->setCurrentSource('twig');

        try {
            return $this->permissionResolver->hasPermission(
                $user->getId(),
                $this->activeTenantProvider->getActiveTenantId(),
                $permission,
            );
        } finally {
            $this->dataStore?->setCurrentSource(null);
        }
    }
}
