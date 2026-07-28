<?php

declare(strict_types=1);

namespace GardenManager\Tests\Seller\Application\Query;

use GardenManager\Seller\Application\Query\ListSellersQuery;
use GardenManager\Seller\Application\Query\ListSellersQueryHandler;
use GardenManager\Seller\Domain\Seller;
use GardenManager\Seller\Domain\SellerRepositoryInterface;
use GardenManager\Shared\Domain\Pagination\PaginatedResult;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class ListSellersQueryHandlerTest extends TestCase
{
    #[Test]
    public function returnsPaginatedResultWithViews(): void
    {
        $tenantId = new Ulid();
        $seller = Seller::create('Test', 'test@example.com', $tenantId);

        $repoResult = new PaginatedResult([$seller], 1, 10, 1);

        $repo = $this->createMock(SellerRepositoryInterface::class);
        $repo->expects(self::once())
            ->method('findPaginated')
            ->with(1, ListSellersQuery::DEFAULT_LIMIT)
            ->willReturn($repoResult);

        $handler = new ListSellersQueryHandler($repo);

        $result = $handler(new ListSellersQuery(actorUserId: new Ulid(), tenantId: new Ulid()));

        self::assertCount(1, $result->items);
        self::assertSame('Test', $result->items[0]->name);
        self::assertSame('test@example.com', $result->items[0]->email);
        self::assertSame(1, $result->currentPage);
        self::assertSame(10, $result->perPage);
        self::assertSame(1, $result->totalItems);
    }

    #[Test]
    public function passesCustomPageAndLimit(): void
    {
        $repoResult = new PaginatedResult([], 3, 5, 0);

        $repo = $this->createMock(SellerRepositoryInterface::class);
        $repo->expects(self::once())
            ->method('findPaginated')
            ->with(3, 5)
            ->willReturn($repoResult);

        $handler = new ListSellersQueryHandler($repo);

        $result = $handler(new ListSellersQuery(actorUserId: new Ulid(), tenantId: new Ulid(), page: 3, limit: 5));

        self::assertEquals($repoResult, $result);
    }
}
