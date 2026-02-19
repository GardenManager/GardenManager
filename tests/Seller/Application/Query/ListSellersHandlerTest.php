<?php

declare(strict_types=1);

namespace GardenManager\Tests\Seller\Application\Query;

use GardenManager\Seller\Application\Query\ListSellersHandler;
use GardenManager\Seller\Application\Query\ListSellersQuery;
use GardenManager\Seller\Domain\Seller;
use GardenManager\Seller\Domain\SellerRepositoryInterface;
use GardenManager\Shared\Domain\Pagination\PaginatedResult;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class ListSellersHandlerTest extends TestCase
{
    #[Test]
    public function returnsPaginatedResultWithViews(): void
    {
        $ownerId = new Ulid();
        $seller = Seller::create('Test', 'test@example.com', $ownerId);

        $repoResult = new PaginatedResult([$seller], 1, 10, 1);

        $repo = $this->createMock(SellerRepositoryInterface::class);
        $repo->expects(self::once())
            ->method('findByOwnerIdPaginated')
            ->with($ownerId, 1, ListSellersQuery::DEFAULT_LIMIT)
            ->willReturn($repoResult);

        $handler = new ListSellersHandler($repo);

        $result = $handler(new ListSellersQuery($ownerId));

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
        $ownerId = new Ulid();
        $repoResult = new PaginatedResult([], 3, 5, 0);

        $repo = $this->createMock(SellerRepositoryInterface::class);
        $repo->expects(self::once())
            ->method('findByOwnerIdPaginated')
            ->with($ownerId, 3, 5)
            ->willReturn($repoResult);

        $handler = new ListSellersHandler($repo);

        $result = $handler(new ListSellersQuery($ownerId, page: 3, limit: 5));

        self::assertEquals($repoResult, $result);
    }
}
