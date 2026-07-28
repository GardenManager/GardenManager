<?php

declare(strict_types=1);

namespace GardenManager\Permission\Application\Command;

use Doctrine\ORM\OptimisticLockException;
use GardenManager\Permission\Application\Service\PermissionCacheInvalidatorInterface;
use GardenManager\Permission\Domain\Exception\PermissionException;
use GardenManager\Tenant\Domain\Persistence\TenantRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class RemoveMemberPermissionOverrideCommandHandler
{
    public function __construct(
        private TenantRepositoryInterface $tenantRepository,
        private PermissionCacheInvalidatorInterface $cacheInvalidator,
    ) {
    }

    public function __invoke(RemoveMemberPermissionOverrideCommand $command): void
    {
        $tenant = $this->tenantRepository->getById($command->tenantId);
        $config = $tenant->getPermissionsConfig()->withoutUserOverride($command->userId->toString(), $command->permission);

        $tenant->updatePermissionsConfig($config);

        try {
            $this->tenantRepository->save($tenant);
        } catch (OptimisticLockException) {
            throw PermissionException::concurrentModification();
        }

        $this->cacheInvalidator->invalidateForTenant($command->tenantId);
    }
}
