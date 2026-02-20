<?php

declare(strict_types=1);

namespace GardenManager\Auth\Application\Command;

use GardenManager\Auth\Domain\AuthOidc;
use GardenManager\Auth\Domain\AuthOidcRepositoryInterface;
use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Auth\Domain\AuthUserRepositoryInterface;
use GardenManager\Tenant\Application\Service\TenantProvisioningService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class ProvisionOidcUserHandler
{
    public function __construct(
        private AuthUserRepositoryInterface $authUserRepository,
        private AuthOidcRepositoryInterface $authOidcRepository,
        private TenantProvisioningService $tenantProvisioningService,
    ) {
    }

    public function __invoke(ProvisionOidcUserCommand $command): void
    {
        $user = AuthUser::createFromOidc(
            $command->userId,
            $command->email,
            $command->displayName,
        );

        $link = AuthOidc::create(
            $command->linkId,
            $user,
            $command->provider,
            $command->subject,
        );

        $this->authUserRepository->save($user);
        $this->authOidcRepository->save($link);
        $this->tenantProvisioningService->provisionPersonalTenant($user->getId(), $command->displayName . "'s Garden");
    }
}
