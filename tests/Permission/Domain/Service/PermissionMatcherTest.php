<?php

declare(strict_types=1);

namespace GardenManager\Tests\Permission\Domain\Service;

use GardenManager\Permission\Domain\Service\PermissionMatcher;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class PermissionMatcherTest extends TestCase
{
    private PermissionMatcher $matcher;

    protected function setUp(): void
    {
        $this->matcher = new PermissionMatcher();
    }

    #[Test]
    public function exactMatch(): void
    {
        self::assertTrue($this->matcher->matches('plant.edit', 'plant.edit'));
    }

    #[Test]
    public function exactMismatch(): void
    {
        self::assertFalse($this->matcher->matches('plant.edit', 'plant.delete'));
    }

    #[Test]
    public function doubleStarMatchesEverything(): void
    {
        self::assertTrue($this->matcher->matches('**', 'plant.edit'));
        self::assertTrue($this->matcher->matches('**', 'seller.create'));
        self::assertTrue($this->matcher->matches('**', 'anything'));
    }

    #[Test]
    public function singleSegmentWildcard(): void
    {
        self::assertTrue($this->matcher->matches('plant.*', 'plant.edit'));
        self::assertTrue($this->matcher->matches('plant.*', 'plant.create'));
        self::assertTrue($this->matcher->matches('plant.*', 'plant.delete'));
    }

    #[Test]
    public function wildcardDoesNotMatchDifferentEntity(): void
    {
        self::assertFalse($this->matcher->matches('plant.*', 'seller.edit'));
    }

    #[Test]
    public function wildcardOnFirstSegment(): void
    {
        self::assertTrue($this->matcher->matches('*.edit', 'plant.edit'));
        self::assertTrue($this->matcher->matches('*.edit', 'seller.edit'));
    }

    #[Test]
    public function allWildcardSegments(): void
    {
        self::assertTrue($this->matcher->matches('*.*', 'plant.edit'));
        self::assertTrue($this->matcher->matches('*.*', 'seller.create'));
    }

    #[Test]
    public function segmentCountMustMatch(): void
    {
        self::assertFalse($this->matcher->matches('plant.*', 'plant'));
        self::assertFalse($this->matcher->matches('plant', 'plant.edit'));
    }

    #[Test]
    public function trailingDoubleStarMatchesMultipleSegments(): void
    {
        self::assertTrue($this->matcher->matches('tenant.**', 'tenant.edit'));
        self::assertTrue($this->matcher->matches('tenant.**', 'tenant.edit.raw'));
        self::assertTrue($this->matcher->matches('tenant.**', 'tenant.view'));
    }

    #[Test]
    public function trailingDoubleStarDoesNotMatchDifferentPrefix(): void
    {
        self::assertFalse($this->matcher->matches('tenant.**', 'plant.edit'));
        self::assertFalse($this->matcher->matches('tenant.**', 'seller.view'));
    }

    #[Test]
    public function trailingDoubleStarRequiresAtLeastOneRemainingSegment(): void
    {
        // 'tenant.**' should not match bare 'tenant' (** needs at least one segment)
        self::assertFalse($this->matcher->matches('tenant.**', 'tenant'));
    }

    #[Test]
    public function specificityOfExactMatch(): void
    {
        self::assertGreaterThan(
            $this->matcher->specificity('plant.*'),
            $this->matcher->specificity('plant.edit'),
        );
    }

    #[Test]
    public function specificityOfDoubleStarIsLowest(): void
    {
        self::assertLessThan(
            $this->matcher->specificity('plant.*'),
            $this->matcher->specificity('**'),
        );
    }

    #[Test]
    public function specificityOrder(): void
    {
        $doubleStar = $this->matcher->specificity('**');
        $wildcard = $this->matcher->specificity('plant.*');
        $exact = $this->matcher->specificity('plant.edit');

        self::assertLessThan($wildcard, $doubleStar);
        self::assertLessThan($exact, $wildcard);
    }

    #[Test]
    public function specificityOrderWithTrailingDoubleStar(): void
    {
        $doubleStar = $this->matcher->specificity('**');
        $trailingDoubleStar = $this->matcher->specificity('tenant.**');
        $singleWildcard = $this->matcher->specificity('tenant.edit.*');
        $threeSegmentExact = $this->matcher->specificity('tenant.edit.raw');

        self::assertLessThan($trailingDoubleStar, $doubleStar);
        self::assertLessThan($singleWildcard, $trailingDoubleStar);
        self::assertLessThan($threeSegmentExact, $singleWildcard);
    }

    #[Test]
    public function evaluateReturnsMostSpecificMatch(): void
    {
        $resolved = [
            'plant.*' => true,
            'plant.delete' => false,
        ];

        self::assertTrue($this->matcher->evaluate($resolved, 'plant.edit'));
        self::assertFalse($this->matcher->evaluate($resolved, 'plant.delete'));
    }

    #[Test]
    public function evaluateReturnsFalseWhenNoMatch(): void
    {
        $resolved = ['plant.view' => true];

        self::assertFalse($this->matcher->evaluate($resolved, 'seller.view'));
    }

    #[Test]
    public function evaluateWithTrailingDoubleStarUsesSpecificity(): void
    {
        $resolved = [
            'tenant.**' => true,
            'tenant.edit.raw' => false,
        ];

        self::assertTrue($this->matcher->evaluate($resolved, 'tenant.edit'));
        self::assertFalse($this->matcher->evaluate($resolved, 'tenant.edit.raw'));
    }
}
