<?php

declare(strict_types=1);

namespace GardenManager\Shared\Infrastructure\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use GardenManager\Shared\Domain\Security\ActiveTenantProviderInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, priority: -10)]
final readonly class TenantFilterListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ActiveTenantProviderInterface $activeTenantProvider,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$this->activeTenantProvider->hasActiveTenant()) {
            return;
        }

        $tenantId = $this->activeTenantProvider->getActiveTenantId();
        $filter = $this->entityManager->getFilters()->enable('tenant');
        $filter->setParameter('tenantId', $tenantId->toRfc4122());
    }
}
