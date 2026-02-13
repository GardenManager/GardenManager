<?php

declare(strict_types=1);

namespace GardenManager\Tests\Plant\Domain;

use GardenManager\Plant\Domain\Entity\Plant;
use GardenManager\Plant\Domain\Enum\LifecycleEnum;
use GardenManager\Plant\Domain\Security\PlantAccessChecker;
use GardenManager\Shared\Domain\Exception\EntityOwnershipException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

final class PlantAccessCheckerTest extends TestCase
{
    #[Test]
    public function passesWhenOwnerMatches(): void
    {
        $ownerId = new Ulid();
        $plant = Plant::create(
            ownerId: $ownerId,
            localName: 'Test Plant',
            isHybrid: false,
            lifecycle: LifecycleEnum::ANNUAL,
        );

        $checker = new PlantAccessChecker();
        $checker->ensureOwnership($plant, $ownerId);

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function throwsWhenNotOwner(): void
    {
        $ownerId = new Ulid();
        $differentUserId = new Ulid();
        $plant = Plant::create(
            ownerId: $ownerId,
            localName: 'Test Plant',
            isHybrid: false,
            lifecycle: LifecycleEnum::ANNUAL,
        );

        $checker = new PlantAccessChecker();

        $this->expectException(EntityOwnershipException::class);

        $checker->ensureOwnership($plant, $differentUserId);
    }
}
