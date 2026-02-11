<?php

declare(strict_types=1);

namespace GardenManager\Tests\Seller\Domain;

use GardenManager\Seller\Domain\Seller;
use GardenManager\Shared\Domain\ValueObject\Address;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

final class SellerTest extends TestCase
{
    #[Test]
    public function createFromPrimitives(): void
    {
        $ownerId = new Ulid();
        $seller = Seller::create(
            name: 'John Garden',
            email: 'john@example.com',
            ownerId: $ownerId,
            phone: '+1234567890',
            description: 'A local gardener',
            address: new Address('123 Main St', 'Springfield', '62704', 'US'),
        );

        self::assertSame('John Garden', $seller->getName());
        self::assertSame('john@example.com', $seller->getEmail());
        self::assertSame('+1234567890', $seller->getPhone());
        self::assertSame('A local gardener', $seller->getDescription());
        self::assertNotNull($seller->getAddress());
        self::assertSame('123 Main St', $seller->getAddress()->street);
        self::assertTrue($ownerId->equals($seller->getOwnerId()));
        self::assertNull($seller->getDeletedAt());
    }

    #[Test]
    public function updateFromPrimitives(): void
    {
        $seller = Seller::create(name: 'Old Name', email: 'old@example.com', ownerId: new Ulid());

        $seller->update(
            name: 'New Name',
            email: 'new@example.com',
            phone: '+9876543210',
        );

        self::assertSame('New Name', $seller->getName());
        self::assertSame('new@example.com', $seller->getEmail());
        self::assertSame('+9876543210', $seller->getPhone());
    }

    #[Test]
    public function rejectsInvalidEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Seller::create(name: 'Test', email: 'not-an-email', ownerId: new Ulid());
    }

    #[Test]
    public function createWithCustomId(): void
    {
        $id = new Ulid();
        $seller = Seller::create(name: 'Test', email: 'test@example.com', ownerId: new Ulid(), id: $id);

        self::assertSame($id, $seller->getId());
    }

    #[Test]
    public function nullPhoneIsHandled(): void
    {
        $seller = Seller::create(name: 'Test', email: 'test@example.com', ownerId: new Ulid());

        self::assertNull($seller->getPhone());
    }
}
