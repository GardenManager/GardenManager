<?php

namespace GardenManager\Tests\Auth\Application\Command;

use GardenManager\Auth\Application\Command\RegisterUserCommand;
use GardenManager\Auth\Application\Command\RegisterUserHandler;
use GardenManager\Auth\Application\EmailVerificationServiceInterface;
use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Auth\Domain\AuthUserRepositoryInterface;
use GardenManager\Auth\Domain\Exception\AuthException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Uid\Ulid;

final class RegisterUserHandlerTest extends TestCase
{
    #[Test]
    public function createsUserWithHashedPassword(): void
    {
        $userId = new Ulid();
        $savedUser = null;

        $repo = $this->createMock(AuthUserRepositoryInterface::class);
        $repo->method('findByEmail')->willReturn(null);
        $repo->expects(self::once())
            ->method('save')
            ->willReturnCallback(function (AuthUser $user) use (&$savedUser): void {
                $savedUser = $user;
            });

        $hasher = $this->createStub(UserPasswordHasherInterface::class);
        $hasher->method('hashPassword')->willReturn('hashed_password');

        $emailService = $this->createMock(EmailVerificationServiceInterface::class);
        $emailService->expects(self::never())->method('sendVerificationEmail');

        $handler = new RegisterUserHandler($repo, $hasher, $emailService, false);

        $handler(new RegisterUserCommand($userId, 'test@example.com', 'Test User', 'password123'));

        self::assertInstanceOf(AuthUser::class, $savedUser);
        self::assertSame($userId, $savedUser->getId());
        self::assertSame('test@example.com', $savedUser->getEmail());
        self::assertSame('Test User', $savedUser->getDisplayName());
        self::assertSame('hashed_password', $savedUser->getPassword());
        self::assertTrue($savedUser->isVerified());
    }

    #[Test]
    public function sendsVerificationEmailWhenRequired(): void
    {
        $repo = $this->createMock(AuthUserRepositoryInterface::class);
        $repo->method('findByEmail')->willReturn(null);
        $repo->expects(self::once())->method('save');

        $hasher = $this->createStub(UserPasswordHasherInterface::class);
        $hasher->method('hashPassword')->willReturn('hashed_password');

        $emailService = $this->createMock(EmailVerificationServiceInterface::class);
        $emailService->expects(self::once())->method('sendVerificationEmail');

        $handler = new RegisterUserHandler($repo, $hasher, $emailService, true);

        $handler(new RegisterUserCommand(new Ulid(), 'test@example.com', 'Test', 'password123'));
    }

    #[Test]
    public function rejectsDuplicateEmail(): void
    {
        $existingUser = AuthUser::createWithPassword(new Ulid(), 'taken@example.com', 'Existing', 'pw');

        $repo = $this->createStub(AuthUserRepositoryInterface::class);
        $repo->method('findByEmail')->willReturn($existingUser);

        $hasher = $this->createStub(UserPasswordHasherInterface::class);
        $hasher->method('hashPassword')->willReturn('hashed_password');

        $emailService = $this->createStub(EmailVerificationServiceInterface::class);

        $handler = new RegisterUserHandler($repo, $hasher, $emailService, false);

        $this->expectException(AuthException::class);

        $handler(new RegisterUserCommand(new Ulid(), 'taken@example.com', 'Test', 'password123'));
    }
}
