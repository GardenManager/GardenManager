<?php

declare(strict_types=1);

namespace GardenManager\Auth\Application\Command;

use GardenManager\Auth\Domain\AuthUserRepositoryInterface;
use GardenManager\Auth\Domain\Exception\AuthException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class VerifyEmailHandler
{
    public function __construct(
        private AuthUserRepositoryInterface $authUserRepository,
    ) {
    }

    public function __invoke(VerifyEmailCommand $command): void
    {
        $user = $this->authUserRepository->findById($command->userId);

        if ($user === null) {
            throw AuthException::userNotFoundById($command->userId);
        }

        $user->verify();
        $this->authUserRepository->save($user);
    }
}
