<?php

declare(strict_types=1);

namespace GardenManager\Tests\Shared\Domain\ValueObject;

use GardenManager\Shared\Domain\ValueObject\Address;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class AddressTest extends TestCase
{
    #[Test]
    public function constructsWithAllFields(): void
    {
        $address = new Address('123 Main St', 'Springfield', '62704', 'US');

        self::assertSame('123 Main St', $address->street);
        self::assertSame('Springfield', $address->city);
        self::assertSame('62704', $address->postalCode);
        self::assertSame('US', $address->country);
    }

    #[Test]
    public function toArrayReturnsCorrectStructure(): void
    {
        $address = new Address('123 Main St', 'Springfield', '62704', 'US');

        self::assertSame([
            'street' => '123 Main St',
            'city' => 'Springfield',
            'postalCode' => '62704',
            'country' => 'US',
        ], $address->toArray());
    }
}
