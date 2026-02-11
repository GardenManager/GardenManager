<?php

declare(strict_types=1);

namespace GardenManager\Tests\Seller\Application\Command;

use GardenManager\Seller\Application\Command\DeleteSellerCommand;
use GardenManager\Seller\Application\Command\DeleteSellerHandler;
use GardenManager\Seller\Domain\Exception\SellerException;
use GardenManager\Seller\Domain\Seller;
use GardenManager\Seller\Domain\SellerRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

final class DeleteSellerHandlerTest extends TestCase
{
    #[Test]
    public function softDeletesSeller(): void
    {
        $ownerId = new Ulid();
        $sellerId = new Ulid();

        $seller = Seller::create(name: 'Test', email: 'test@example.com', ownerId: $ownerId, id: $sellerId);

        $repo = $this->createMock(SellerRepositoryInterface::class);
        $repo->method('getByIdForOwner')->with($sellerId, $ownerId)->willReturn($seller);
        $repo->expects(self::once())->method('save');

        $handler = new DeleteSellerHandler($repo);

        $handler(new DeleteSellerCommand(
            sellerId: $sellerId,
            ownerId: $ownerId,
        ));

        self::assertTrue($seller->isDeleted());
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

        $handler = new DeleteSellerHandler($repo);

        $this->expectException(SellerException::class);

        $handler(new DeleteSellerCommand(
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

        $handler = new DeleteSellerHandler($repo);

        $this->expectException(SellerException::class);

        $handler(new DeleteSellerCommand(
            sellerId: $sellerId,
            ownerId: $ownerId,
        ));
    }
}
