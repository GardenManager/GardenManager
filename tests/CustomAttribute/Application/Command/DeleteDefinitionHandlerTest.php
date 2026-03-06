<?php

declare(strict_types=1);

namespace GardenManager\Tests\CustomAttribute\Application\Command;

use GardenManager\CustomAttribute\Application\Command\DeleteDefinitionCommand;
use GardenManager\CustomAttribute\Application\Command\DeleteDefinitionHandler;
use GardenManager\CustomAttribute\Domain\Entity\CustomAttributeDefinition;
use GardenManager\CustomAttribute\Domain\Enum\AttributeTypeEnum;
use GardenManager\CustomAttribute\Domain\Exception\CustomAttributeException;
use GardenManager\CustomAttribute\Domain\Persistence\CustomAttributeDefinitionRepositoryInterface;
use GardenManager\Shared\Domain\Exception\TenantAccessException;
use GardenManager\Shared\Domain\Security\TenantAccessChecker;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Ulid;

#[Group('unit')]
final class DeleteDefinitionHandlerTest extends TestCase
{
    #[Test]
    public function deletesDefinition(): void
    {
        $tenantId = new Ulid();
        $definitionId = new Ulid();

        $definition = CustomAttributeDefinition::create(
            tenantId: $tenantId,
            entityType: 'plant',
            name: 'test',
            label: 'Test',
            type: AttributeTypeEnum::STRING,
            definitionId: $definitionId,
        );

        $repo = $this->createMock(CustomAttributeDefinitionRepositoryInterface::class);
        $repo->method('getById')->with($definitionId)->willReturn($definition);
        $repo->expects(self::once())->method('remove')->with($definition);

        $handler = new DeleteDefinitionHandler($repo, new TenantAccessChecker());

        $handler(new DeleteDefinitionCommand(
            definitionId: $definitionId,
            tenantId: $tenantId,
            actorUserId: new Ulid(),
        ));
    }

    #[Test]
    public function throwsNotFoundWhenMissing(): void
    {
        $definitionId = new Ulid();

        $repo = $this->createStub(CustomAttributeDefinitionRepositoryInterface::class);
        $repo->method('getById')->willThrowException(
            CustomAttributeException::definitionNotFound($definitionId),
        );

        $handler = new DeleteDefinitionHandler($repo, new TenantAccessChecker());

        $this->expectException(CustomAttributeException::class);

        $handler(new DeleteDefinitionCommand(
            definitionId: $definitionId,
            tenantId: new Ulid(),
            actorUserId: new Ulid(),
        ));
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

        $handler = new DeleteDefinitionHandler($repo, new TenantAccessChecker());

        $this->expectException(TenantAccessException::class);

        $handler(new DeleteDefinitionCommand(
            definitionId: $definitionId,
            tenantId: $tenantId,
            actorUserId: new Ulid(),
        ));
    }
}
