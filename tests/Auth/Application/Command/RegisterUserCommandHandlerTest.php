<?php

declare(strict_types=1);

namespace GardenManager\Tests\Auth\Application\Command;

use GardenManager\Auth\Application\Command\RegisterUserCommand;
use GardenManager\Auth\Application\Command\RegisterUserCommandHandler;
use GardenManager\Auth\Application\EmailVerificationServiceInterface;
use GardenManager\Auth\Domain\Entity\AuthUser;
use GardenManager\Auth\Domain\Exception\AuthException;
use GardenManager\Auth\Domain\Persistence\AuthUserRepositoryInterface;
use GardenManager\Shared\Application\CommandDispatcherInterface;
use GardenManager\Tenant\Application\Service\TenantProvisioningService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class RegisterUserCommandHandlerTest extends TestCase
{
    private TenantProvisioningService $tenantProvisioning;

    protected function setUp(): void
    {
        $this->tenantProvisioning = new TenantProvisioningService(
            $this->createStub(CommandDispatcherInterface::class),
        );
    }

    #[Test]
    public function createsUserWithHashedPassword(): void
    {
        $userId = new Ulid();
        $savedUser = null;

        $repo = $this->createMock(AuthUserRepositoryInterface::class);
        $repo->method('findByEmail')->willReturn(null);
        $repo->expects(self::once())
            ->method('save')
            ->willReturnCallback(static function (AuthUser $user) use (&$savedUser): void {
                $savedUser = $user;
            });

        $hasher = $this->createStub(UserPasswordHasherInterface::class);
        $hasher->method('hashPassword')->willReturn('hashed_password');

        $emailService = $this->createMock(EmailVerificationServiceInterface::class);
        $emailService->expects(self::never())->method('sendVerificationEmail');

        $handler = new RegisterUserCommandHandler($repo, $hasher, $emailService, $this->tenantProvisioning, false);

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

        $handler = new RegisterUserCommandHandler($repo, $hasher, $emailService, $this->tenantProvisioning, true);

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

        $handler = new RegisterUserCommandHandler($repo, $hasher, $emailService, $this->tenantProvisioning, false);

        $this->expectException(AuthException::class);

        $handler(new RegisterUserCommand(new Ulid(), 'taken@example.com', 'Test', 'password123'));
    }
}
