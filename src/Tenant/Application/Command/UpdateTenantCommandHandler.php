<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Application\Command;

use GardenManager\Tenant\Domain\Security\TenantAuthorizationChecker;
use GardenManager\Tenant\Domain\TenantRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class UpdateTenantCommandHandler
{
    public function __construct(
        private TenantRepositoryInterface $tenantRepository,
        private TenantAuthorizationChecker $authorizationChecker,
    )
    {
    }

    public function __invoke(UpdateTenantCommand $command): void
    {
        $this->authorizationChecker->ensureOwner($command->tenantId, $command->actorUserId);

        $tenant = $this->tenantRepository->getById($command->tenantId);
        $tenant->update($command->name);
        $this->tenantRepository->save($tenant);
    }
}
