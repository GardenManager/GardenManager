<?php

declare(strict_types=1);

namespace GardenManager\Permission\Application\Command;

use Doctrine\ORM\OptimisticLockException;
use GardenManager\Permission\Application\Service\PermissionCacheInvalidatorInterface;
use GardenManager\Permission\Application\Service\PermissionConfigValidator;
use GardenManager\Permission\Domain\Exception\PermissionException;
use GardenManager\Permission\Domain\ValueObject\TenantPermissionConfig;
use GardenManager\Tenant\Domain\TenantRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class UpdateRawPermissionsCommandHandler
{
    public function __construct(
        private TenantRepositoryInterface $tenantRepository,
        private PermissionCacheInvalidatorInterface $cacheInvalidator,
        private PermissionConfigValidator $configValidator,
    ) {
    }

    public function __invoke(UpdateRawPermissionsCommand $command): void
    {
        $newConfig = TenantPermissionConfig::fromArray($command->configData);
        $this->configValidator->validate($newConfig);

        $tenant = $this->tenantRepository->getById($command->tenantId);

        $tenant->updatePermissionsConfig($newConfig);

        try {
            $this->tenantRepository->save($tenant);
        } catch (OptimisticLockException) {
            throw PermissionException::concurrentModification();
        }

        $this->cacheInvalidator->invalidateForTenant($command->tenantId);
    }
}
