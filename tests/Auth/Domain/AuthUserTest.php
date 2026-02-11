<?php

declare(strict_types=1);

namespace GardenManager\Tests\Auth\Domain;

use GardenManager\Auth\Domain\AuthUser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

final class AuthUserTest extends TestCase
{
    #[Test]
    public function createWithPasswordSetsFieldsCorrectly(): void
    {
        $id = new Ulid();
        $user = AuthUser::createWithPassword($id, 'test@example.com', 'Test User', 'hashed_pw');

        self::assertSame($id, $user->getId());
        self::assertSame('test@example.com', $user->getEmail());
        self::assertSame('Test User', $user->getDisplayName());
        self::assertSame('hashed_pw', $user->getPassword());
        self::assertTrue($user->hasPassword());
        self::assertFalse($user->isVerified());
    }

    #[Test]
    public function createFromOidcSetsFieldsCorrectly(): void
    {
        $id = new Ulid();
        $user = AuthUser::createFromOidc($id, 'oidc@example.com', 'OIDC User');

        self::assertSame($id, $user->getId());
        self::assertSame('oidc@example.com', $user->getEmail());
        self::assertSame('OIDC User', $user->getDisplayName());
        self::assertNull($user->getPassword());
        self::assertFalse($user->hasPassword());
        self::assertTrue($user->isVerified());
    }

    #[Test]
    public function verifySetsIsVerifiedToTrue(): void
    {
        $user = AuthUser::createWithPassword(new Ulid(), 'test@example.com', 'Test', 'pw');

        self::assertFalse($user->isVerified());

        $user->verify();

        self::assertTrue($user->isVerified());
    }

    #[Test]
    public function getRolesAlwaysIncludesRoleUser(): void
    {
        $user = AuthUser::createWithPassword(new Ulid(), 'test@example.com', 'Test', 'pw');

        self::assertContains('ROLE_USER', $user->getRoles());
    }

    #[Test]
    public function getUserIdentifierReturnsEmail(): void
    {
        $user = AuthUser::createWithPassword(new Ulid(), 'test@example.com', 'Test', 'pw');

        self::assertSame('test@example.com', $user->getUserIdentifier());
    }
}
