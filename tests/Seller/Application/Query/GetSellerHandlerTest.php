<?php

declare(strict_types=1);

namespace GardenManager\Tests\Seller\Application\Query;

use GardenManager\Seller\Application\Query\GetSellerHandler;
use GardenManager\Seller\Application\Query\GetSellerQuery;
use GardenManager\Seller\Domain\Exception\SellerException;
use GardenManager\Seller\Domain\Seller;
use GardenManager\Seller\Domain\SellerRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

final class GetSellerHandlerTest extends TestCase
{
    #[Test]
    public function returnsSellerDetailView(): void
    {
        $ownerId = new Ulid();
        $sellerId = new Ulid();

        $seller = Seller::create(name: 'Test Seller', email: 'test@example.com', ownerId: $ownerId, id: $sellerId);

        $repo = $this->createStub(SellerRepositoryInterface::class);
        $repo->method('getByIdForOwner')->with($sellerId, $ownerId)->willReturn($seller);

        $handler = new GetSellerHandler($repo);

        $result = $handler(new GetSellerQuery(
            sellerId: $sellerId,
            ownerId: $ownerId,
        ));

        self::assertSame('Test Seller', $result->name);
        self::assertSame('test@example.com', $result->email);
    }

    #[Test]
    public function throwsNotFoundWhenSellerMissing(): void
    {
        $sellerId = new Ulid();
        $ownerId = new Ulid();

        $repo = $this->createStub(SellerRepositoryInterface::class);
        $repo->method('getByIdForOwner')->willThrowException(
            SellerException::notFoundById($sellerId),
        );

        $handler = new GetSellerHandler($repo);

        $this->expectException(SellerException::class);

        $handler(new GetSellerQuery(
            sellerId: $sellerId,
            ownerId: $ownerId,
        ));
    }

    #[Test]
    public function throwsAccessDeniedWhenNotOwner(): void
    {
        $sellerId = new Ulid();
        $ownerId = new Ulid();

        $repo = $this->createStub(SellerRepositoryInterface::class);
        $repo->method('getByIdForOwner')->willThrowException(
            SellerException::notOwned($sellerId, $ownerId),
        );

        $handler = new GetSellerHandler($repo);

        $this->expectException(SellerException::class);

        $handler(new GetSellerQuery(
            sellerId: $sellerId,
            ownerId: $ownerId,
        ));
    }
}
