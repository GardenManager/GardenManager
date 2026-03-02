<?php

declare(strict_types=1);

namespace GardenManager\Permission\Infrastructure\Twig;

use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Permission\Domain\Service\PermissionResolverInterface;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\RuntimeExtensionInterface;

final readonly class PermissionRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private PermissionResolverInterface $permissionResolver,
        private ActiveTenantProviderInterface $activeTenantProvider,
        private Security $security,
    ) {}

    public function hasPermission(string $permission): bool
    {
        /** @var AuthUser $user */
        $user = $this->security->getUser();

        if (!$user instanceof AuthUser) {
            return false;
        }

        if (!$this->activeTenantProvider->hasActiveTenant()) {
            return false;
        }

        return $this->permissionResolver->hasPermission(
            $user->getId(),
            $this->activeTenantProvider->getActiveTenantId(),
            $permission,
        );
    }
}
