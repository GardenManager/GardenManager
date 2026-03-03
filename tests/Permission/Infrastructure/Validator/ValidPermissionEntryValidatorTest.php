<?php

declare(strict_types=1);

namespace GardenManager\Tests\Permission\Infrastructure\Validator;

use GardenManager\Permission\Infrastructure\Validator\ValidPermissionEntry;
use GardenManager\Permission\Infrastructure\Validator\ValidPermissionEntryValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/** @extends ConstraintValidatorTestCase<ValidPermissionEntryValidator> */
#[Group('unit')]
final class ValidPermissionEntryValidatorTest extends ConstraintValidatorTestCase
{
    #[Test]
    public function nullValueRaisesBlankViolation(): void
    {
        $constraint = new ValidPermissionEntry();

        $this->validator->validate(null, $constraint);

        $this->buildViolation($constraint->blankMessage)
            ->assertRaised();
    }

    #[Test]
    public function emptyStringRaisesBlankViolation(): void
    {
        $constraint = new ValidPermissionEntry();

        $this->validator->validate('', $constraint);

        $this->buildViolation($constraint->blankMessage)
            ->assertRaised();
    }

    #[Test]
    #[DataProvider('validPrefixedEntries')]
    public function validPrefixedEntryPasses(string $entry): void
    {
        $this->validator->validate($entry, new ValidPermissionEntry());

        $this->assertNoViolation();
    }

    /** @return iterable<string, array{string}> */
    public static function validPrefixedEntries(): iterable
    {
        yield 'grant exact' => ['+plant.edit'];
        yield 'deny exact' => ['-plant.edit'];
        yield 'grant wildcard' => ['+plant.*'];
        yield 'deny wildcard' => ['-seller.*'];
        yield 'grant multi-segment' => ['+tenant.edit.raw'];
    }

    #[Test]
    public function missingPrefixRaisesPrefixViolation(): void
    {
        $constraint = new ValidPermissionEntry();

        $this->validator->validate('plant.edit', $constraint);

        $this->buildViolation($constraint->prefixMessage)
            ->assertRaised();
    }

    #[Test]
    #[DataProvider('tooShortPrefixedEntries')]
    public function tooShortPrefixedEntryRaisesInvalidViolation(string $entry): void
    {
        $constraint = new ValidPermissionEntry();

        $this->validator->validate($entry, $constraint);

        $this->buildViolation($constraint->invalidPermissionMessage)
            ->assertRaised();
    }

    /** @return iterable<string, array{string}> */
    public static function tooShortPrefixedEntries(): iterable
    {
        yield 'plus only' => ['+'];
        yield 'minus only' => ['-'];
        yield 'plus with one char' => ['+a'];
        yield 'minus with one char' => ['-b'];
    }

    #[Test]
    #[DataProvider('validUnprefixedPermissions')]
    public function validUnprefixedPermissionPasses(string $permission): void
    {
        $this->validator->validate($permission, new ValidPermissionEntry(prefixed: false));

        $this->assertNoViolation();
    }

    /** @return iterable<string, array{string}> */
    public static function validUnprefixedPermissions(): iterable
    {
        yield 'exact' => ['plant.edit'];
        yield 'wildcard' => ['plant.*'];
        yield 'double star' => ['**'];
        yield 'single word' => ['admin'];
    }

    #[Test]
    public function nullUnprefixedRaisesBlankViolation(): void
    {
        $constraint = new ValidPermissionEntry(prefixed: false);

        $this->validator->validate(null, $constraint);

        $this->buildViolation($constraint->blankMessage)
            ->assertRaised();
    }

    #[Test]
    public function emptyUnprefixedRaisesBlankViolation(): void
    {
        $constraint = new ValidPermissionEntry(prefixed: false);

        $this->validator->validate('', $constraint);

        $this->buildViolation($constraint->blankMessage)
            ->assertRaised();
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        return new ValidPermissionEntryValidator();
    }
}
