<?php

declare(strict_types=1);

namespace GardenManager\Permission\Application\Command;

use Doctrine\ORM\OptimisticLockException;
use GardenManager\Permission\Application\Service\PermissionCacheInvalidatorInterface;
use GardenManager\Permission\Application\Service\PermissionConfigValidator;
use GardenManager\Permission\Domain\Exception\PermissionException;
use GardenManager\Tenant\Domain\Exception\TenantException;
use GardenManager\Tenant\Domain\Persistence\TenantMembershipRepositoryInterface;
use GardenManager\Tenant\Domain\Persistence\TenantRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class AddMemberPermissionOverrideCommandHandler
{
    public function __construct(
        private TenantRepositoryInterface $tenantRepository,
        private PermissionCacheInvalidatorInterface $cacheInvalidator,
        private PermissionConfigValidator $configValidator,
        private TenantMembershipRepositoryInterface $membershipRepository,
    ) {
    }

    public function __invoke(AddMemberPermissionOverrideCommand $command): void
    {
        $membership = $this->membershipRepository->findByTenantIdAndUserId($command->tenantId, $command->userId);

        if ($membership === null) {
            throw TenantException::notAMember($command->tenantId, $command->userId);
        }

        $tenant = $this->tenantRepository->getById($command->tenantId);
        $config = $tenant->getPermissionsConfig()->withUserOverride($command->userId->toString(), $command->prefixedPermission);

        $this->configValidator->validate($config);

        $tenant->updatePermissionsConfig($config);

        try {
            $this->tenantRepository->save($tenant);
        } catch (OptimisticLockException) {
            throw PermissionException::concurrentModification();
        }

        $this->cacheInvalidator->invalidateForTenant($command->tenantId);
    }
}
