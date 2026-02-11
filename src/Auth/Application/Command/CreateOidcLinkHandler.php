<?php

namespace GardenManager\Auth\Application\Command;

use GardenManager\Auth\Domain\AuthOidc;
use GardenManager\Auth\Domain\AuthOidcRepositoryInterface;
use GardenManager\Auth\Domain\AuthUserRepositoryInterface;
use GardenManager\Auth\Domain\Exception\AuthException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class CreateOidcLinkHandler
{
    public function __construct(
        private AuthUserRepositoryInterface $authUserRepository,
        private AuthOidcRepositoryInterface $authOidcRepository,
    ) {
    }

    public function __invoke(CreateOidcLinkCommand $command): void
    {
        $authUser = $this->authUserRepository->findById($command->userId);

        if ($authUser === null) {
            throw AuthException::userNotFoundById($command->userId);
        }

        $link = AuthOidc::create(
            $command->linkId,
            $authUser,
            $command->provider,
            $command->subject,
        );

        $this->authOidcRepository->save($link);
    }
}
