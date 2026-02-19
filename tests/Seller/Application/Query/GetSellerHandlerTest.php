<?php

declare(strict_types=1);

namespace GardenManager\Tests\Seller\Application\Query;

use GardenManager\Seller\Application\Query\GetSellerHandler;
use GardenManager\Seller\Application\Query\GetSellerQuery;
use GardenManager\Seller\Domain\Seller;
use GardenManager\Seller\Domain\SellerAccessChecker;
use GardenManager\Seller\Domain\SellerRepositoryInterface;
use GardenManager\Shared\Domain\Exception\EntityNotFoundException;
use GardenManager\Shared\Domain\Exception\EntityOwnershipException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class GetSellerHandlerTest extends TestCase
{
    #[Test]
    public function returnsSellerDetailView(): void
    {
        $ownerId = new Ulid();
        $sellerId = new Ulid();

        $seller = Seller::create(name: 'Test Seller', email: 'test@example.com', ownerId: $ownerId, id: $sellerId);

        $repo = $this->createStub(SellerRepositoryInterface::class);
        $repo->method('getById')->with($sellerId)->willReturn($seller);

        $handler = new GetSellerHandler($repo, new SellerAccessChecker());

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
        $repo->method('getById')->willThrowException(
            EntityNotFoundException::fromEntityClassNameAndId(Seller::class, $sellerId),
        );

        $handler = new GetSellerHandler($repo, new SellerAccessChecker());

        $this->expectException(EntityNotFoundException::class);

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
        $differentOwnerId = new Ulid();

        $seller = Seller::create(name: 'Test', email: 'test@example.com', ownerId: $differentOwnerId, id: $sellerId);

        $repo = $this->createStub(SellerRepositoryInterface::class);
        $repo->method('getById')->willReturn($seller);

        $handler = new GetSellerHandler($repo, new SellerAccessChecker());

        $this->expectException(EntityOwnershipException::class);

        $handler(new GetSellerQuery(
            sellerId: $sellerId,
            ownerId: $ownerId,
        ));
    }
}
