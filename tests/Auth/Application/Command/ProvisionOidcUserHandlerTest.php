<?php

namespace GardenManager\Tests\Auth\Application\Command;

use GardenManager\Auth\Application\Command\ProvisionOidcUserCommand;
use GardenManager\Auth\Application\Command\ProvisionOidcUserHandler;
use GardenManager\Auth\Domain\AuthOidc;
use GardenManager\Auth\Domain\AuthOidcRepositoryInterface;
use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Auth\Domain\AuthUserRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

final class ProvisionOidcUserHandlerTest extends TestCase
{
    #[Test]
    public function createsUserAndLink(): void
    {
        $userId = new Ulid();
        $linkId = new Ulid();
        $savedUser = null;
        $savedLink = null;

        $userRepo = $this->createMock(AuthUserRepositoryInterface::class);
        $userRepo->expects(self::once())
            ->method('save')
            ->willReturnCallback(function (AuthUser $user) use (&$savedUser): void {
                $savedUser = $user;
            });

        $oidcRepo = $this->createMock(AuthOidcRepositoryInterface::class);
        $oidcRepo->expects(self::once())
            ->method('save')
            ->willReturnCallback(function (AuthOidc $link) use (&$savedLink): void {
                $savedLink = $link;
            });

        $handler = new ProvisionOidcUserHandler($userRepo, $oidcRepo);

        $handler(new ProvisionOidcUserCommand($userId, $linkId, 'oidc@example.com', 'OIDC User', 'oidc', 'sub-123'));

        self::assertInstanceOf(AuthUser::class, $savedUser);
        self::assertSame($userId, $savedUser->getId());
        self::assertSame('oidc@example.com', $savedUser->getEmail());
        self::assertTrue($savedUser->isVerified());
        self::assertFalse($savedUser->hasPassword());

        self::assertInstanceOf(AuthOidc::class, $savedLink);
        self::assertSame($linkId, $savedLink->getId());
        self::assertSame('oidc', $savedLink->getProvider());
        self::assertSame('sub-123', $savedLink->getSubject());
    }
}
