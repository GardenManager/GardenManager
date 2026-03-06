<?php

declare(strict_types=1);

namespace GardenManager\Tests\CustomAttribute\Domain\Entity;

use GardenManager\CustomAttribute\Domain\Entity\CustomAttributeDefinition;
use GardenManager\CustomAttribute\Domain\Enum\AttributeTypeEnum;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class CustomAttributeDefinitionTest extends TestCase
{
    #[Test]
    public function createDefinition(): void
    {
        $tenantId = new Ulid();
        $definitionId = new Ulid();

        $definition = CustomAttributeDefinition::create(
            tenantId: $tenantId,
            entityType: 'plant',
            name: 'companion_plants',
            label: 'Companion Plants',
            type: AttributeTypeEnum::STRING,
            required: true,
            sortOrder: 5,
            definitionId: $definitionId,
        );

        self::assertTrue($definitionId->equals($definition->getId()));
        self::assertTrue($tenantId->equals($definition->getTenantId()));
        self::assertSame('plant', $definition->getEntityType());
        self::assertSame('companion_plants', $definition->getName());
        self::assertSame('Companion Plants', $definition->getLabel());
        self::assertSame(AttributeTypeEnum::STRING, $definition->getType());
        self::assertTrue($definition->isRequired());
        self::assertSame(5, $definition->getSortOrder());
        self::assertNull($definition->getOptions());
    }

    #[Test]
    public function createWithAutoId(): void
    {
        $definition = CustomAttributeDefinition::create(
            tenantId: new Ulid(),
            entityType: 'plant',
            name: 'test',
            label: 'Test',
            type: AttributeTypeEnum::INTEGER,
        );

        self::assertTrue(Ulid::isValid($definition->getId()->toString()));
    }

    #[Test]
    public function createSelectWithOptions(): void
    {
        $options = ['low', 'medium', 'high'];

        $definition = CustomAttributeDefinition::create(
            tenantId: new Ulid(),
            entityType: 'plant',
            name: 'allergen_rating',
            label: 'Allergen Rating',
            type: AttributeTypeEnum::SELECT,
            options: $options,
        );

        self::assertSame($options, $definition->getOptions());
    }

    #[Test]
    public function updateMutableFields(): void
    {
        $definition = CustomAttributeDefinition::create(
            tenantId: new Ulid(),
            entityType: 'plant',
            name: 'test_attr',
            label: 'Old Label',
            type: AttributeTypeEnum::STRING,
            required: false,
            sortOrder: 0,
        );

        $definition->update(
            label: 'New Label',
            required: true,
            sortOrder: 10,
            options: ['a', 'b'],
        );

        self::assertSame('New Label', $definition->getLabel());
        self::assertTrue($definition->isRequired());
        self::assertSame(10, $definition->getSortOrder());
        self::assertSame(['a', 'b'], $definition->getOptions());
        // Immutable fields unchanged
        self::assertSame('test_attr', $definition->getName());
        self::assertSame('plant', $definition->getEntityType());
        self::assertSame(AttributeTypeEnum::STRING, $definition->getType());
    }

    #[Test]
    public function defaultValues(): void
    {
        $definition = CustomAttributeDefinition::create(
            tenantId: new Ulid(),
            entityType: 'plant',
            name: 'test',
            label: 'Test',
            type: AttributeTypeEnum::BOOLEAN,
        );

        self::assertFalse($definition->isRequired());
        self::assertSame(0, $definition->getSortOrder());
        self::assertNull($definition->getOptions());
    }
}
