<?php

declare(strict_types=1);

namespace GardenManager\Tests\Seller\Application\Command;

use GardenManager\Seller\Application\Command\CreateSellerCommand;
use GardenManager\Seller\Application\Command\CreateSellerCommandHandler;
use GardenManager\Seller\Domain\Seller;
use GardenManager\Seller\Domain\SellerRepositoryInterface;
use GardenManager\Shared\Application\Dto\AddressData;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class CreateSellerCommandHandlerTest extends TestCase
{
    #[Test]
    public function createsSeller(): void
    {
        $sellerId = new Ulid();
        $tenantId = new Ulid();
        $savedSeller = null;

        $repo = $this->createMock(SellerRepositoryInterface::class);
        $repo->expects(self::once())
            ->method('save')
            ->willReturnCallback(static function (Seller $seller) use (&$savedSeller): void {
                $savedSeller = $seller;
            });

        $handler = new CreateSellerCommandHandler($repo);

        $command = new CreateSellerCommand(
            sellerId: $sellerId,
            tenantId: $tenantId,
            actorUserId: new Ulid(),
            name: 'John Garden',
            email: 'john@example.com',
            phone: '+1234567890',
            description: 'A gardener',
        );

        $handler($command);

        self::assertInstanceOf(Seller::class, $savedSeller);
        self::assertSame('John Garden', $savedSeller->getName());
        self::assertSame('john@example.com', $savedSeller->getEmail());
        self::assertSame('+1234567890', $savedSeller->getPhone());
        self::assertSame($sellerId, $savedSeller->getId());
    }

    #[Test]
    public function createsSellerWithAddress(): void
    {
        $sellerId = new Ulid();
        $tenantId = new Ulid();
        $savedSeller = null;

        $repo = $this->createMock(SellerRepositoryInterface::class);
        $repo->expects(self::once())
            ->method('save')
            ->willReturnCallback(static function (Seller $seller) use (&$savedSeller): void {
                $savedSeller = $seller;
            });

        $handler = new CreateSellerCommandHandler($repo);

        $command = new CreateSellerCommand(
            sellerId: $sellerId,
            tenantId: $tenantId,
            actorUserId: new Ulid(),
            name: 'Test',
            email: 'test@example.com',
            address: new AddressData('123 Main St', 'Springfield', '62704', 'US'),
        );

        $handler($command);

        self::assertNotNull($savedSeller->getAddress());
        self::assertSame('123 Main St', $savedSeller->getAddress()->street);
        self::assertSame('Springfield', $savedSeller->getAddress()->city);
    }
}
