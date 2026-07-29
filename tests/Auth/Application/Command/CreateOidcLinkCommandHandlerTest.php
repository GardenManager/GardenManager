<?php

declare(strict_types=1);

namespace GardenManager\Tests\Auth\Application\Command;

use GardenManager\Auth\Application\Command\CreateOidcLinkCommand;
use GardenManager\Auth\Application\Command\CreateOidcLinkCommandHandler;
use GardenManager\Auth\Domain\Entity\AuthOidc;
use GardenManager\Auth\Domain\Entity\AuthUser;
use GardenManager\Auth\Domain\Exception\AuthException;
use GardenManager\Auth\Domain\Persistence\AuthOidcRepositoryInterface;
use GardenManager\Auth\Domain\Persistence\AuthUserRepositoryInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class CreateOidcLinkCommandHandlerTest extends TestCase
{
    #[Test]
    public function createsLinkForExistingUser(): void
    {
        $userId = new Ulid();
        $linkId = new Ulid();
        $user = AuthUser::createFromOidc($userId, 'user@example.com', 'User');
        $savedLink = null;

        $userRepo = $this->createStub(AuthUserRepositoryInterface::class);
        $userRepo->method('findById')->willReturn($user);

        $oidcRepo = $this->createMock(AuthOidcRepositoryInterface::class);
        $oidcRepo->expects(self::once())
            ->method('save')
            ->willReturnCallback(static function (AuthOidc $link) use (&$savedLink): void {
                $savedLink = $link;
            });

        $handler = new CreateOidcLinkCommandHandler($userRepo, $oidcRepo);
        $handler(new CreateOidcLinkCommand($linkId, $userId, 'oidc', 'sub-456'));

        self::assertInstanceOf(AuthOidc::class, $savedLink);
        self::assertSame($linkId, $savedLink->getId());
        self::assertSame($user, $savedLink->getUser());
        self::assertSame('oidc', $savedLink->getProvider());
        self::assertSame('sub-456', $savedLink->getSubject());
    }

    #[Test]
    public function throwsWhenUserNotFound(): void
    {
        $userRepo = $this->createStub(AuthUserRepositoryInterface::class);
        $userRepo->method('findById')->willReturn(null);

        $oidcRepo = $this->createStub(AuthOidcRepositoryInterface::class);

        $handler = new CreateOidcLinkCommandHandler($userRepo, $oidcRepo);

        $this->expectException(AuthException::class);

        $handler(new CreateOidcLinkCommand(new Ulid(), new Ulid(), 'oidc', 'sub'));
    }
}
