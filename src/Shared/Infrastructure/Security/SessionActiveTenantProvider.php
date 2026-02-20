<?php

declare(strict_types=1);

namespace GardenManager\Shared\Infrastructure\Security;

use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Uid\Ulid;
use \LogicException;

final class SessionActiveTenantProvider implements ActiveTenantProviderInterface
{
    private const string SESSION_KEY = '_active_tenant_id';

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function getActiveTenantId(): Ulid
    {
        $session = $this->requestStack->getSession();
        $tenantId = $session->get(self::SESSION_KEY);

        if ($tenantId === null) {
            throw new LogicException('No active tenant set in session.');
        }

        return Ulid::fromString($tenantId);
    }

    public function setActiveTenantId(Ulid $tenantId): void
    {
        $this->requestStack->getSession()->set(self::SESSION_KEY, (string) $tenantId);
    }

    public function hasActiveTenant(): bool
    {
        return $this->requestStack->getSession()->has(self::SESSION_KEY);
    }
}
