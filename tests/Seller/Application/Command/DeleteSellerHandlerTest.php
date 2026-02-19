<?php

declare(strict_types=1);

namespace GardenManager\Tests\Seller\Application\Command;

use GardenManager\Seller\Application\Command\DeleteSellerCommand;
use GardenManager\Seller\Application\Command\DeleteSellerHandler;
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
final class DeleteSellerHandlerTest extends TestCase
{
    #[Test]
    public function softDeletesSeller(): void
    {
        $ownerId = new Ulid();
        $sellerId = new Ulid();

        $seller = Seller::create(name: 'Test', email: 'test@example.com', ownerId: $ownerId, id: $sellerId);

        $repo = $this->createMock(SellerRepositoryInterface::class);
        $repo->method('getById')->with($sellerId)->willReturn($seller);
        $repo->expects(self::once())->method('save');

        $handler = new DeleteSellerHandler($repo, new SellerAccessChecker());

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
        $repo->method('getById')->willThrowException(
            EntityNotFoundException::fromEntityClassNameAndId(Seller::class, $sellerId),
        );

        $handler = new DeleteSellerHandler($repo, new SellerAccessChecker());

        $this->expectException(EntityNotFoundException::class);

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
        $differentOwnerId = new Ulid();

        $seller = Seller::create(name: 'Test', email: 'test@example.com', ownerId: $differentOwnerId, id: $sellerId);

        $repo = $this->createStub(SellerRepositoryInterface::class);
        $repo->method('getById')->willReturn($seller);

        $handler = new DeleteSellerHandler($repo, new SellerAccessChecker());

        $this->expectException(EntityOwnershipException::class);

        $handler(new DeleteSellerCommand(
            sellerId: $sellerId,
            ownerId: $ownerId,
        ));
    }
}
