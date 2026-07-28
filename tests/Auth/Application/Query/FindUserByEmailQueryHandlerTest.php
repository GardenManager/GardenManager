<?php

declare(strict_types=1);

namespace GardenManager\Tests\Auth\Application\Query;

use GardenManager\Auth\Application\Query\AuthUserSummaryView;
use GardenManager\Auth\Application\Query\FindUserByEmailQuery;
use GardenManager\Auth\Application\Query\FindUserByEmailQueryHandler;
use GardenManager\Auth\Domain\AuthUser;
use GardenManager\Auth\Domain\AuthUserRepositoryInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class FindUserByEmailQueryHandlerTest extends TestCase
{
    #[Test]
    public function returnsViewWhenUserExists(): void
    {
        $user = AuthUser::createWithPassword(new Ulid(), 'user@example.com', 'User', 'hashed');

        $repo = $this->createStub(AuthUserRepositoryInterface::class);
        $repo->method('findByEmail')->willReturn($user);

        $handler = new FindUserByEmailQueryHandler($repo);
        $result = $handler(new FindUserByEmailQuery('user@example.com'));

        self::assertInstanceOf(AuthUserSummaryView::class, $result);
        self::assertSame('user@example.com', $result->email);
        self::assertSame('User', $result->displayName);
        self::assertTrue($result->hasPassword);
        self::assertFalse($result->isVerified);
    }

    #[Test]
    public function returnsNullWhenUserDoesNotExist(): void
    {
        $repo = $this->createStub(AuthUserRepositoryInterface::class);
        $repo->method('findByEmail')->willReturn(null);

        $handler = new FindUserByEmailQueryHandler($repo);
        $result = $handler(new FindUserByEmailQuery('nobody@example.com'));

        self::assertNull($result);
    }
}
