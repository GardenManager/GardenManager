<?php

declare(strict_types=1);

namespace GardenManager\Tests\Shared\Application\Attribute;

use GardenManager\Shared\Application\Attribute\NoPermissionRequired;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class NoPermissionRequiredTest extends TestCase
{
    #[Test]
    public function exposesTheGivenReason(): void
    {
        $attribute = new NoPermissionRequired(reason: 'Pre-authentication flow.');

        self::assertSame('Pre-authentication flow.', $attribute->reason);
    }

    #[Test]
    public function rejectsAnEmptyReason(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new NoPermissionRequired(reason: '');
    }

    #[Test]
    public function rejectsAWhitespaceOnlyReason(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new NoPermissionRequired(reason: '   ');
    }
}
