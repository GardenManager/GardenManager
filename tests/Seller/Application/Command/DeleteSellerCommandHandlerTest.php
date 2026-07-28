<?php

declare(strict_types=1);

namespace GardenManager\Tests\Seller\Application\Command;

use GardenManager\Seller\Application\Command\DeleteSellerCommand;
use GardenManager\Seller\Application\Command\DeleteSellerCommandHandler;
use GardenManager\Seller\Domain\Seller;
use GardenManager\Seller\Domain\SellerRepositoryInterface;
use GardenManager\Shared\Domain\Exception\EntityNotFoundException;
use GardenManager\Shared\Domain\Exception\TenantAccessException;
use GardenManager\Shared\Domain\Security\TenantAccessChecker;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class DeleteSellerCommandHandlerTest extends TestCase
{
    #[Test]
    public function softDeletesSeller(): void
    {
        $tenantId = new Ulid();
        $sellerId = new Ulid();

        $seller = Seller::create(name: 'Test', email: 'test@example.com', tenantId: $tenantId, sellerId: $sellerId);

        $repo = $this->createMock(SellerRepositoryInterface::class);
        $repo->method('getById')->with($sellerId)->willReturn($seller);
        $repo->expects(self::once())->method('save');

        $handler = new DeleteSellerCommandHandler($repo, new TenantAccessChecker());

        $handler(new DeleteSellerCommand(
            sellerId: $sellerId,
            tenantId: $tenantId,
            actorUserId: new Ulid(),
        ));

        self::assertTrue($seller->isDeleted());
    }

    #[Test]
    public function throwsNotFoundWhenSellerMissing(): void
    {
        $sellerId = new Ulid();
        $tenantId = new Ulid();

        $repo = $this->createStub(SellerRepositoryInterface::class);
        $repo->method('getById')->willThrowException(
            EntityNotFoundException::fromEntityClassNameAndId(Seller::class, $sellerId),
        );

        $handler = new DeleteSellerCommandHandler($repo, new TenantAccessChecker());

        $this->expectException(EntityNotFoundException::class);

        $handler(new DeleteSellerCommand(
            sellerId: $sellerId,
            tenantId: $tenantId,
            actorUserId: new Ulid(),
        ));
    }

    #[Test]
    public function throwsAccessDeniedWhenNotTenant(): void
    {
        $sellerId = new Ulid();
        $tenantId = new Ulid();
        $differentTenantId = new Ulid();

        $seller = Seller::create(name: 'Test', email: 'test@example.com', tenantId: $differentTenantId, sellerId: $sellerId);

        $repo = $this->createStub(SellerRepositoryInterface::class);
        $repo->method('getById')->willReturn($seller);

        $handler = new DeleteSellerCommandHandler($repo, new TenantAccessChecker());

        $this->expectException(TenantAccessException::class);

        $handler(new DeleteSellerCommand(
            sellerId: $sellerId,
            tenantId: $tenantId,
            actorUserId: new Ulid(),
        ));
    }
}
