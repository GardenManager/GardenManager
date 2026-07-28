<?php

declare(strict_types=1);

namespace GardenManager\Tests\Auth\Application\Query;

use GardenManager\Auth\Application\Query\FindOidcLinkQuery;
use GardenManager\Auth\Application\Query\FindOidcLinkQueryHandler;
use GardenManager\Auth\Application\Query\OidcLinkView;
use GardenManager\Auth\Domain\AuthOidc;
use GardenManager\Auth\Domain\AuthOidcRepositoryInterface;
use GardenManager\Auth\Domain\AuthUser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class FindOidcLinkQueryHandlerTest extends TestCase
{
    #[Test]
    public function returnsViewWhenLinkExists(): void
    {
        $user = AuthUser::createFromOidc(new Ulid(), 'user@example.com', 'User');
        $link = AuthOidc::create(new Ulid(), $user, 'oidc', 'sub-123');

        $repo = $this->createStub(AuthOidcRepositoryInterface::class);
        $repo->method('findByProviderAndSubject')->willReturn($link);

        $handler = new FindOidcLinkQueryHandler($repo);
        $result = $handler(new FindOidcLinkQuery('oidc', 'sub-123'));

        self::assertInstanceOf(OidcLinkView::class, $result);
        self::assertSame('user@example.com', $result->userEmail);
        self::assertSame('oidc', $result->provider);
        self::assertSame('sub-123', $result->subject);
    }

    #[Test]
    public function returnsNullWhenLinkDoesNotExist(): void
    {
        $repo = $this->createStub(AuthOidcRepositoryInterface::class);
        $repo->method('findByProviderAndSubject')->willReturn(null);

        $handler = new FindOidcLinkQueryHandler($repo);
        $result = $handler(new FindOidcLinkQuery('oidc', 'nonexistent'));

        self::assertNull($result);
    }
}
