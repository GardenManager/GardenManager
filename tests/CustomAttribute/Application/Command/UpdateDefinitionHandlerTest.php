<?php

declare(strict_types=1);

namespace GardenManager\Tests\CustomAttribute\Application\Command;

use GardenManager\CustomAttribute\Application\Command\UpdateDefinitionCommand;
use GardenManager\CustomAttribute\Application\Command\UpdateDefinitionHandler;
use GardenManager\CustomAttribute\Domain\Entity\CustomAttributeDefinition;
use GardenManager\CustomAttribute\Domain\Enum\AttributeTypeEnum;
use GardenManager\CustomAttribute\Domain\Persistence\CustomAttributeDefinitionRepositoryInterface;
use GardenManager\Shared\Domain\Exception\TenantAccessException;
use GardenManager\Shared\Domain\Security\TenantAccessChecker;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class UpdateDefinitionHandlerTest extends TestCase
{
    #[Test]
    public function updatesDefinition(): void
    {
        $tenantId = new Ulid();
        $definitionId = new Ulid();

        $definition = CustomAttributeDefinition::create(
            tenantId: $tenantId,
            entityType: 'plant',
            name: 'test_attr',
            label: 'Old Label',
            type: AttributeTypeEnum::STRING,
            definitionId: $definitionId,
        );

        $repo = $this->createMock(CustomAttributeDefinitionRepositoryInterface::class);
        $repo->method('getById')->with($definitionId)->willReturn($definition);
        $repo->expects(self::once())->method('save');

        $handler = new UpdateDefinitionHandler($repo, new TenantAccessChecker());

        $handler(new UpdateDefinitionCommand(
            definitionId: $definitionId,
            tenantId: $tenantId,
            actorUserId: new Ulid(),
            label: 'New Label',
            required: true,
            sortOrder: 10,
            options: ['a', 'b'],
        ));

        self::assertSame('New Label', $definition->getLabel());
        self::assertTrue($definition->isRequired());
        self::assertSame(10, $definition->getSortOrder());
        self::assertSame(['a', 'b'], $definition->getOptions());
    }

    #[Test]
    public function throwsAccessDeniedWhenNotTenant(): void
    {
        $tenantId = new Ulid();
        $differentTenantId = new Ulid();
        $definitionId = new Ulid();

        $definition = CustomAttributeDefinition::create(
            tenantId: $differentTenantId,
            entityType: 'plant',
            name: 'test',
            label: 'Test',
            type: AttributeTypeEnum::STRING,
            definitionId: $definitionId,
        );

        $repo = $this->createStub(CustomAttributeDefinitionRepositoryInterface::class);
        $repo->method('getById')->willReturn($definition);

        $handler = new UpdateDefinitionHandler($repo, new TenantAccessChecker());

        $this->expectException(TenantAccessException::class);

        $handler(new UpdateDefinitionCommand(
            definitionId: $definitionId,
            tenantId: $tenantId,
            actorUserId: new Ulid(),
            label: 'Hacked',
        ));
    }
}
