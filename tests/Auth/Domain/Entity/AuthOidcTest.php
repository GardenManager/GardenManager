<?php

declare(strict_types=1);

namespace GardenManager\Tests\Auth\Domain\Entity;

use GardenManager\Auth\Domain\Entity\AuthOidc;
use GardenManager\Auth\Domain\Entity\AuthUser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
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
