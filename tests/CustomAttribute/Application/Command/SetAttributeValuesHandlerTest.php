<?php

declare(strict_types=1);

namespace GardenManager\Tests\CustomAttribute\Application\Command;

use GardenManager\CustomAttribute\Application\Command\SetAttributeValuesCommand;
use GardenManager\CustomAttribute\Application\Command\SetAttributeValuesHandler;
use GardenManager\CustomAttribute\Domain\Entity\CustomAttributeDefinition;
use GardenManager\CustomAttribute\Domain\Entity\CustomAttributeValue;
use GardenManager\CustomAttribute\Domain\Enum\AttributeTypeEnum;
use GardenManager\CustomAttribute\Domain\Persistence\CustomAttributeDefinitionRepositoryInterface;
use GardenManager\CustomAttribute\Domain\Persistence\CustomAttributeValueRepositoryInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class SetAttributeValuesHandlerTest extends TestCase
{
    #[Test]
    public function createsNewValues(): void
    {
        $tenantId = new Ulid();
        $entityId = new Ulid();

        $definition = CustomAttributeDefinition::create(
            tenantId: $tenantId,
            entityType: 'plant',
            name: 'note',
            label: 'Note',
            type: AttributeTypeEnum::STRING,
        );
        $defIdString = (string) $definition->getId();

        $defRepo = $this->createStub(CustomAttributeDefinitionRepositoryInterface::class);
        $defRepo->method('findByEntityType')->willReturn([$definition]);

        $valueRepo = $this->createMock(CustomAttributeValueRepositoryInterface::class);
        $valueRepo->method('findIndexedByDefinitionForEntity')->willReturn([]);
        $valueRepo->expects(self::once())
            ->method('save')
            ->willReturnCallback(static function (CustomAttributeValue $value) use ($definition): void {
                self::assertSame('Hello world', $value->getValue());
                self::assertSame($definition, $value->getDefinition());
            });

        $handler = new SetAttributeValuesHandler($defRepo, $valueRepo);

        $handler(new SetAttributeValuesCommand(
            tenantId: $tenantId,
            actorUserId: new Ulid(),
            entityType: 'plant',
            entityId: $entityId,
            values: [$defIdString => 'Hello world'],
        ));
    }

    #[Test]
    public function updatesExistingValues(): void
    {
        $tenantId = new Ulid();
        $entityId = new Ulid();

        $definition = CustomAttributeDefinition::create(
            tenantId: $tenantId,
            entityType: 'plant',
            name: 'note',
            label: 'Note',
            type: AttributeTypeEnum::STRING,
        );
        $defIdString = (string) $definition->getId();

        $existingValue = CustomAttributeValue::create(
            definition: $definition,
            entityType: 'plant',
            entityId: $entityId,
            value: 'Old value',
        );

        $defRepo = $this->createStub(CustomAttributeDefinitionRepositoryInterface::class);
        $defRepo->method('findByEntityType')->willReturn([$definition]);

        $valueRepo = $this->createMock(CustomAttributeValueRepositoryInterface::class);
        $valueRepo->method('findIndexedByDefinitionForEntity')->willReturn([$defIdString => $existingValue]);
        $valueRepo->expects(self::once())->method('save');
        $valueRepo->expects(self::never())->method('remove');

        $handler = new SetAttributeValuesHandler($defRepo, $valueRepo);

        $handler(new SetAttributeValuesCommand(
            tenantId: $tenantId,
            actorUserId: new Ulid(),
            entityType: 'plant',
            entityId: $entityId,
            values: [$defIdString => 'New value'],
        ));

        self::assertSame('New value', $existingValue->getValue());
    }

    #[Test]
    public function removesValueWhenEmpty(): void
    {
        $tenantId = new Ulid();
        $entityId = new Ulid();

        $definition = CustomAttributeDefinition::create(
            tenantId: $tenantId,
            entityType: 'plant',
            name: 'note',
            label: 'Note',
            type: AttributeTypeEnum::STRING,
        );
        $defIdString = (string) $definition->getId();

        $existingValue = CustomAttributeValue::create(
            definition: $definition,
            entityType: 'plant',
            entityId: $entityId,
            value: 'To be removed',
        );

        $defRepo = $this->createStub(CustomAttributeDefinitionRepositoryInterface::class);
        $defRepo->method('findByEntityType')->willReturn([$definition]);

        $valueRepo = $this->createMock(CustomAttributeValueRepositoryInterface::class);
        $valueRepo->method('findIndexedByDefinitionForEntity')->willReturn([$defIdString => $existingValue]);
        $valueRepo->expects(self::once())->method('remove')->with($existingValue);
        $valueRepo->expects(self::never())->method('save');

        $handler = new SetAttributeValuesHandler($defRepo, $valueRepo);

        $handler(new SetAttributeValuesCommand(
            tenantId: $tenantId,
            actorUserId: new Ulid(),
            entityType: 'plant',
            entityId: $entityId,
            values: [$defIdString => null],
        ));
    }

    #[Test]
    public function skipsEmptyValueWhenNoExisting(): void
    {
        $tenantId = new Ulid();

        $definition = CustomAttributeDefinition::create(
            tenantId: $tenantId,
            entityType: 'plant',
            name: 'note',
            label: 'Note',
            type: AttributeTypeEnum::STRING,
        );
        $defIdString = (string) $definition->getId();

        $defRepo = $this->createStub(CustomAttributeDefinitionRepositoryInterface::class);
        $defRepo->method('findByEntityType')->willReturn([$definition]);

        $valueRepo = $this->createMock(CustomAttributeValueRepositoryInterface::class);
        $valueRepo->method('findIndexedByDefinitionForEntity')->willReturn([]);
        $valueRepo->expects(self::never())->method('save');
        $valueRepo->expects(self::never())->method('remove');

        $handler = new SetAttributeValuesHandler($defRepo, $valueRepo);

        $handler(new SetAttributeValuesCommand(
            tenantId: $tenantId,
            actorUserId: new Ulid(),
            entityType: 'plant',
            entityId: new Ulid(),
            values: [$defIdString => ''],
        ));
    }
}
