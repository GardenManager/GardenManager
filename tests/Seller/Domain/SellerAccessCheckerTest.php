<?php

declare(strict_types=1);

namespace GardenManager\Tests\Seller\Domain;

use GardenManager\Seller\Domain\Seller;
use GardenManager\Seller\Domain\SellerAccessChecker;
use GardenManager\Shared\Domain\Exception\EntityOwnershipException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

final class SellerAccessCheckerTest extends TestCase
{
    #[Test]
    public function passesWhenOwnerMatches(): void
    {
        $ownerId = new Ulid();
        $seller = Seller::create(name: 'Test', email: 'test@example.com', ownerId: $ownerId);

        $checker = new SellerAccessChecker();
        $checker->ensureOwnership($seller, $ownerId);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function throwsWhenNotOwner(): void
    {
        $ownerId = new Ulid();
        $differentUserId = new Ulid();
        $seller = Seller::create(name: 'Test', email: 'test@example.com', ownerId: $ownerId);

        $checker = new SellerAccessChecker();

        $this->expectException(EntityOwnershipException::class);

        $checker->ensureOwnership($seller, $differentUserId);
    }
}
