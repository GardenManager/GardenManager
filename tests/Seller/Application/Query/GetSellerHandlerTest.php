<?php

declare(strict_types=1);

namespace GardenManager\Tests\Seller\Application\Query;

use GardenManager\Seller\Application\Query\GetSellerHandler;
use GardenManager\Seller\Application\Query\GetSellerQuery;
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
final class GetSellerHandlerTest extends TestCase
{
    #[Test]
    public function returnsSellerDetailView(): void
    {
        $tenantId = new Ulid();
        $sellerId = new Ulid();

        $seller = Seller::create(name: 'Test Seller', email: 'test@example.com', tenantId: $tenantId, sellerId: $sellerId);

        $repo = $this->createStub(SellerRepositoryInterface::class);
        $repo->method('getById')->willReturn($seller);

        $handler = new GetSellerHandler($repo, new TenantAccessChecker());

        $result = $handler(new GetSellerQuery(
            sellerId: $sellerId,
            tenantId: $tenantId,
        ));

        self::assertSame('Test Seller', $result->name);
        self::assertSame('test@example.com', $result->email);
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

        $handler = new GetSellerHandler($repo, new TenantAccessChecker());

        $this->expectException(EntityNotFoundException::class);

        $handler(new GetSellerQuery(
            sellerId: $sellerId,
            tenantId: $tenantId,
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

        $handler = new GetSellerHandler($repo, new TenantAccessChecker());

        $this->expectException(TenantAccessException::class);

        $handler(new GetSellerQuery(
            sellerId: $sellerId,
            tenantId: $tenantId,
        ));
    }
}
