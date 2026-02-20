<?php

declare(strict_types=1);

namespace GardenManager\Tests\Seller\Application\Command;

use GardenManager\Seller\Application\Command\UpdateSellerCommand;
use GardenManager\Seller\Application\Command\UpdateSellerHandler;
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
final class UpdateSellerHandlerTest extends TestCase
{
    #[Test]
    public function updatesSeller(): void
    {
        $tenantId = new Ulid();
        $sellerId = new Ulid();

        $seller = Seller::create(name: 'Old Name', email: 'old@example.com', tenantId: $tenantId, sellerId: $sellerId);

        $repo = $this->createMock(SellerRepositoryInterface::class);
        $repo->method('getById')->with($sellerId)->willReturn($seller);
        $repo->expects(self::once())->method('save');

        $handler = new UpdateSellerHandler($repo, new TenantAccessChecker());

        $command = new UpdateSellerCommand(
            sellerId: $sellerId,
            tenantId: $tenantId,
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
        $tenantId = new Ulid();

        $repo = $this->createStub(SellerRepositoryInterface::class);
        $repo->method('getById')->willThrowException(
            EntityNotFoundException::fromEntityClassNameAndId(Seller::class, $sellerId),
        );

        $handler = new UpdateSellerHandler($repo, new TenantAccessChecker());

        $this->expectException(EntityNotFoundException::class);

        $handler(new UpdateSellerCommand(
            sellerId: $sellerId,
            tenantId: $tenantId,
            name: 'Test',
            email: 'test@example.com',
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

        $handler = new UpdateSellerHandler($repo, new TenantAccessChecker());

        $this->expectException(TenantAccessException::class);

        $handler(new UpdateSellerCommand(
            sellerId: $sellerId,
            tenantId: $tenantId,
            name: 'Hacked',
            email: 'hack@example.com',
        ));
    }
}
