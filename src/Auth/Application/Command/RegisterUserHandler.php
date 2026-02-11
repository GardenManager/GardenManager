<?php

declare(strict_types=1);

namespace GardenManager\Auth\Application\Command;

use GardenManager\Auth\Application\EmailVerificationServiceInterface;
use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Auth\Domain\AuthUserRepositoryInterface;
use GardenManager\Auth\Domain\Exception\AuthException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class RegisterUserHandler
{
    public function __construct(
        private AuthUserRepositoryInterface $authUserRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private EmailVerificationServiceInterface $emailVerificationService,
        private bool $requireEmailVerification,
    ) {
    }

    public function __invoke(RegisterUserCommand $command): void
    {
        $existing = $this->authUserRepository->findByEmail($command->email);

        if ($existing !== null) {
            throw AuthException::emailAlreadyRegistered($command->email);
        }

        $user = AuthUser::createWithoutPassword(
            $command->userId,
            $command->email,
            $command->displayName,
        );

        $user->setPassword($this->passwordHasher->hashPassword($user, $command->plainPassword));

        if (!$this->requireEmailVerification) {
            $user->verify();
        }

        $this->authUserRepository->save($user);

        if ($this->requireEmailVerification) {
            $this->emailVerificationService->sendVerificationEmail($user);
        }
    }
}
