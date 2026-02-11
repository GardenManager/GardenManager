<?php

namespace GardenManager\Auth\Application\Command;

use GardenManager\Auth\Domain\AuthOidc;
use GardenManager\Auth\Domain\AuthOidcRepositoryInterface;
use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Auth\Domain\AuthUserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class ProvisionOidcUserHandler
{
    public function __construct(
        private AuthUserRepositoryInterface $authUserRepository,
        private AuthOidcRepositoryInterface $authOidcRepository,
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
    }
}
