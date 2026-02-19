<?php

declare(strict_types=1);

namespace GardenManager\Tests\Auth\Application\Command;

use GardenManager\Auth\Application\Command\ConfirmOidcLinkCommand;
use GardenManager\Auth\Application\Command\ConfirmOidcLinkHandler;
use GardenManager\Auth\Domain\AuthOidc;
use GardenManager\Auth\Domain\AuthOidcRepositoryInterface;
use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Auth\Domain\AuthUserRepositoryInterface;
use GardenManager\Auth\Domain\Exception\AuthException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class ConfirmOidcLinkHandlerTest extends TestCase
{
    #[Test]
    public function createsLinkWhenPasswordIsValid(): void
    {
        $user = AuthUser::createWithPassword(new Ulid(), 'user@example.com', 'User', 'hashed');
        $savedLink = null;

        $userRepo = $this->createStub(AuthUserRepositoryInterface::class);
        $userRepo->method('findByEmail')->willReturn($user);

        $oidcRepo = $this->createMock(AuthOidcRepositoryInterface::class);
        $oidcRepo->expects(self::once())
            ->method('save')
            ->willReturnCallback(static function (AuthOidc $link) use (&$savedLink): void {
                $savedLink = $link;
            });

        $hasher = $this->createStub(UserPasswordHasherInterface::class);
        $hasher->method('isPasswordValid')->willReturn(true);

        $handler = new ConfirmOidcLinkHandler($userRepo, $oidcRepo, $hasher);

        $handler(new ConfirmOidcLinkCommand(new Ulid(), 'user@example.com', 'correct-password', 'oidc', 'sub-789'));

        self::assertInstanceOf(AuthOidc::class, $savedLink);
        self::assertSame('oidc', $savedLink->getProvider());
        self::assertSame('sub-789', $savedLink->getSubject());
    }

    #[Test]
    public function throwsOnInvalidPassword(): void
    {
        $user = AuthUser::createWithPassword(new Ulid(), 'user@example.com', 'User', 'hashed');

        $userRepo = $this->createStub(AuthUserRepositoryInterface::class);
        $userRepo->method('findByEmail')->willReturn($user);

        $oidcRepo = $this->createStub(AuthOidcRepositoryInterface::class);

        $hasher = $this->createStub(UserPasswordHasherInterface::class);
        $hasher->method('isPasswordValid')->willReturn(false);

        $handler = new ConfirmOidcLinkHandler($userRepo, $oidcRepo, $hasher);

        $this->expectException(AuthException::class);

        $handler(new ConfirmOidcLinkCommand(new Ulid(), 'user@example.com', 'wrong', 'oidc', 'sub'));
    }

    #[Test]
    public function throwsWhenUserNotFound(): void
    {
        $userRepo = $this->createStub(AuthUserRepositoryInterface::class);
        $userRepo->method('findByEmail')->willReturn(null);

        $oidcRepo = $this->createStub(AuthOidcRepositoryInterface::class);
        $hasher = $this->createStub(UserPasswordHasherInterface::class);

        $handler = new ConfirmOidcLinkHandler($userRepo, $oidcRepo, $hasher);

        $this->expectException(AuthException::class);

        $handler(new ConfirmOidcLinkCommand(new Ulid(), 'no@example.com', 'pw', 'oidc', 'sub'));
    }
}
