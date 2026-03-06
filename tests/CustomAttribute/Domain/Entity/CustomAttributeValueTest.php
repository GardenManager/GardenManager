<?php

declare(strict_types=1);

namespace GardenManager\Tests\CustomAttribute\Domain\Entity;

use DateTimeImmutable;
use GardenManager\CustomAttribute\Domain\Entity\CustomAttributeDefinition;
use GardenManager\CustomAttribute\Domain\Entity\CustomAttributeValue;
use GardenManager\CustomAttribute\Domain\Enum\AttributeTypeEnum;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class CustomAttributeValueTest extends TestCase
{
    #[Test]
    public function createStringValue(): void
    {
        $definition = CustomAttributeDefinition::create(
            tenantId: new Ulid(),
            entityType: 'plant',
            name: 'note',
            label: 'Note',
            type: AttributeTypeEnum::STRING,
        );

        $entityId = new Ulid();
        $value = CustomAttributeValue::create(
            definition: $definition,
            entityType: 'plant',
            entityId: $entityId,
            value: 'Some note',
        );

        self::assertSame('Some note', $value->getValue());
        self::assertSame('plant', $value->getEntityType());
        self::assertTrue($entityId->equals($value->getEntityId()));
        self::assertSame($definition, $value->getDefinition());
    }

    #[Test]
    public function createIntegerValue(): void
    {
        $definition = CustomAttributeDefinition::create(
            tenantId: new Ulid(),
            entityType: 'plant',
            name: 'height',
            label: 'Height',
            type: AttributeTypeEnum::INTEGER,
        );

        $value = CustomAttributeValue::create(
            definition: $definition,
            entityType: 'plant',
            entityId: new Ulid(),
            value: 42,
        );

        self::assertSame(42, $value->getValue());
    }

    #[Test]
    public function createDecimalValue(): void
    {
        $definition = CustomAttributeDefinition::create(
            tenantId: new Ulid(),
            entityType: 'plant',
            name: 'weight',
            label: 'Weight',
            type: AttributeTypeEnum::DECIMAL,
        );

        $value = CustomAttributeValue::create(
            definition: $definition,
            entityType: 'plant',
            entityId: new Ulid(),
            value: '3.14',
        );

        self::assertSame('3.14', $value->getValue());
    }

    #[Test]
    public function createDateValue(): void
    {
        $definition = CustomAttributeDefinition::create(
            tenantId: new Ulid(),
            entityType: 'plant',
            name: 'planted_date',
            label: 'Planted Date',
            type: AttributeTypeEnum::DATE,
        );

        $date = new DateTimeImmutable('2025-06-15');
        $value = CustomAttributeValue::create(
            definition: $definition,
            entityType: 'plant',
            entityId: new Ulid(),
            value: $date,
        );

        self::assertInstanceOf(DateTimeImmutable::class, $value->getValue());
        self::assertSame('2025-06-15', $value->getValue()->format('Y-m-d'));
    }

    #[Test]
    public function createBooleanValue(): void
    {
        $definition = CustomAttributeDefinition::create(
            tenantId: new Ulid(),
            entityType: 'plant',
            name: 'is_edible',
            label: 'Is Edible',
            type: AttributeTypeEnum::BOOLEAN,
        );

        $value = CustomAttributeValue::create(
            definition: $definition,
            entityType: 'plant',
            entityId: new Ulid(),
            value: true,
        );

        self::assertTrue($value->getValue());
    }

    #[Test]
    public function createSelectValue(): void
    {
        $definition = CustomAttributeDefinition::create(
            tenantId: new Ulid(),
            entityType: 'plant',
            name: 'rating',
            label: 'Rating',
            type: AttributeTypeEnum::SELECT,
            options: ['low', 'medium', 'high'],
        );

        $value = CustomAttributeValue::create(
            definition: $definition,
            entityType: 'plant',
            entityId: new Ulid(),
            value: 'medium',
        );

        self::assertSame('medium', $value->getValue());
    }

    #[Test]
    public function updateValue(): void
    {
        $definition = CustomAttributeDefinition::create(
            tenantId: new Ulid(),
            entityType: 'plant',
            name: 'note',
            label: 'Note',
            type: AttributeTypeEnum::STRING,
        );

        $value = CustomAttributeValue::create(
            definition: $definition,
            entityType: 'plant',
            entityId: new Ulid(),
            value: 'Original',
        );

        $value->updateValue('Updated');

        self::assertSame('Updated', $value->getValue());
    }

    #[Test]
    public function createWithCustomId(): void
    {
        $definition = CustomAttributeDefinition::create(
            tenantId: new Ulid(),
            entityType: 'plant',
            name: 'test',
            label: 'Test',
            type: AttributeTypeEnum::STRING,
        );

        $valueId = new Ulid();
        $value = CustomAttributeValue::create(
            definition: $definition,
            entityType: 'plant',
            entityId: new Ulid(),
            value: 'test',
            valueId: $valueId,
        );

        self::assertTrue($valueId->equals($value->getId()));
    }
}
