<?php

declare(strict_types=1);

namespace GardenManager\Tests\CustomAttribute\Application\Command;

use GardenManager\CustomAttribute\Application\Command\CreateDefinitionCommand;
use GardenManager\CustomAttribute\Application\Command\CreateDefinitionHandler;
use GardenManager\CustomAttribute\Domain\Entity\CustomAttributeDefinition;
use GardenManager\CustomAttribute\Domain\Enum\AttributeTypeEnum;
use GardenManager\CustomAttribute\Domain\Exception\CustomAttributeException;
use GardenManager\CustomAttribute\Domain\Persistence\CustomAttributeDefinitionRepositoryInterface;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class CreateDefinitionHandlerTest extends TestCase
{
    #[Test]
    public function createsDefinition(): void
    {
        $definitionId = new Ulid();
        $tenantId = new Ulid();
        $savedDefinition = null;

        $repo = $this->createMock(CustomAttributeDefinitionRepositoryInterface::class);
        $repo->method('existsByTenantEntityTypeAndName')->willReturn(false);
        $repo->expects(self::once())
            ->method('save')
            ->willReturnCallback(static function (CustomAttributeDefinition $definition) use (&$savedDefinition): void {
                $savedDefinition = $definition;
            });

        $handler = new CreateDefinitionHandler($repo);

        $command = new CreateDefinitionCommand(
            definitionId: $definitionId,
            tenantId: $tenantId,
            actorUserId: new Ulid(),
            name: 'companion_plants',
            label: 'Companion Plants',
            entityType: 'plant',
            type: AttributeTypeEnum::STRING,
            required: true,
            sortOrder: 5,
        );

        $handler($command);

        self::assertInstanceOf(CustomAttributeDefinition::class, $savedDefinition);
        self::assertSame('companion_plants', $savedDefinition->getName());
        self::assertSame('Companion Plants', $savedDefinition->getLabel());
        self::assertSame('plant', $savedDefinition->getEntityType());
        self::assertSame(AttributeTypeEnum::STRING, $savedDefinition->getType());
        self::assertTrue($savedDefinition->isRequired());
        self::assertSame(5, $savedDefinition->getSortOrder());
    }

    #[Test]
    public function throwsOnDuplicateName(): void
    {
        $repo = $this->createStub(CustomAttributeDefinitionRepositoryInterface::class);
        $repo->method('existsByTenantEntityTypeAndName')->willReturn(true);

        $handler = new CreateDefinitionHandler($repo);

        $command = new CreateDefinitionCommand(
            definitionId: new Ulid(),
            tenantId: new Ulid(),
            actorUserId: new Ulid(),
            name: 'duplicate_name',
            label: 'Duplicate',
            entityType: 'plant',
            type: AttributeTypeEnum::STRING,
        );

        $this->expectException(CustomAttributeException::class);

        $handler($command);
    }
}
