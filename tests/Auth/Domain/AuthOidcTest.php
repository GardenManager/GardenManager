<?php

declare(strict_types=1);

namespace GardenManager\Tests\Auth\Domain;

use GardenManager\Auth\Domain\AuthOidc;
use GardenManager\Auth\Domain\AuthUser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

final class AuthOidcTest extends TestCase
{
    #[Test]
    public function createSetsFieldsCorrectly(): void
    {
        $linkId = new Ulid();
        $user = AuthUser::createFromOidc(new Ulid(), 'user@example.com', 'User');

        $link = AuthOidc::create($linkId, $user, 'oidc', 'subject-123');

        self::assertSame($linkId, $link->getId());
        self::assertSame($user, $link->getUser());
        self::assertSame('oidc', $link->getProvider());
        self::assertSame('subject-123', $link->getSubject());
    }
}
