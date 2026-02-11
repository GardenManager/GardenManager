<?php

declare(strict_types=1);

namespace GardenManager\Tests\Seller\Application\Command;

use GardenManager\Seller\Application\Command\UpdateSellerCommand;
use GardenManager\Seller\Application\Command\UpdateSellerHandler;
use GardenManager\Seller\Domain\Exception\SellerException;
use GardenManager\Seller\Domain\Seller;
use GardenManager\Seller\Domain\SellerRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

final class UpdateSellerHandlerTest extends TestCase
{
    #[Test]
    public function updatesSeller(): void
    {
        $ownerId = new Ulid();
        $sellerId = new Ulid();

        $seller = Seller::create(name: 'Old Name', email: 'old@example.com', ownerId: $ownerId, id: $sellerId);

        $repo = $this->createMock(SellerRepositoryInterface::class);
        $repo->method('getByIdForOwner')->with($sellerId, $ownerId)->willReturn($seller);
        $repo->expects(self::once())->method('save');

        $handler = new UpdateSellerHandler($repo);

        $command = new UpdateSellerCommand(
            sellerId: $sellerId,
            ownerId: $ownerId,
            name: 'New Name',
            email: 'new@example.com',
        );

        $handler($command);

        self::assertSame('New Name', $seller->getName());
        self::assertSame('new@example.com', $seller->getEmail());
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

        $handler = new UpdateSellerHandler($repo);

        $this->expectException(SellerException::class);

        $handler(new UpdateSellerCommand(
            sellerId: $sellerId,
            ownerId: $ownerId,
            name: 'Test',
            email: 'test@example.com',
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

        $handler = new UpdateSellerHandler($repo);

        $this->expectException(SellerException::class);

        $handler(new UpdateSellerCommand(
            sellerId: $sellerId,
            ownerId: $ownerId,
            name: 'Hacked',
            email: 'hack@example.com',
        ));
    }
}
