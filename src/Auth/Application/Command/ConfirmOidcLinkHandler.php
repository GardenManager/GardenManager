<?php

declare(strict_types=1);

namespace GardenManager\Auth\Application\Command;

use GardenManager\Auth\Domain\AuthOidc;
use GardenManager\Auth\Domain\AuthOidcRepositoryInterface;
use GardenManager\Auth\Domain\AuthUserRepositoryInterface;
use GardenManager\Auth\Domain\Exception\AuthException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class ConfirmOidcLinkHandler
{
    public function __construct(
        private AuthUserRepositoryInterface $authUserRepository,
        private AuthOidcRepositoryInterface $authOidcRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function __invoke(ConfirmOidcLinkCommand $command): void
    {
        $user = $this->authUserRepository->findByEmail($command->email);

        if ($user === null) {
            throw AuthException::userNotFoundByEmail($command->email);
        }

        if (!$this->passwordHasher->isPasswordValid($user, $command->plainPassword)) {
            throw AuthException::invalidPassword();
        }

        $link = AuthOidc::create(
            $command->linkId,
            $user,
            $command->provider,
            $command->subject,
        );

        $this->authOidcRepository->save($link);
    }
}
