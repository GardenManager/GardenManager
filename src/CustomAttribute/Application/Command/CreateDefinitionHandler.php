<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Application\Command;

use GardenManager\CustomAttribute\Domain\Entity\CustomAttributeDefinition;
use GardenManager\CustomAttribute\Domain\Exception\CustomAttributeException;
use GardenManager\CustomAttribute\Domain\Persistence\CustomAttributeDefinitionRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class CreateDefinitionHandler
{
    public function __construct(
        private CustomAttributeDefinitionRepositoryInterface $definitionRepository,
    ) {
    }

    public function __invoke(CreateDefinitionCommand $command): void
    {
        if ($this->definitionRepository->existsByEntityTypeAndName($command->entityType, $command->name)) {
            throw CustomAttributeException::duplicateName($command->entityType, $command->name);
        }

        $definition = CustomAttributeDefinition::create(
            tenantId: $command->tenantId,
            entityType: $command->entityType,
            name: $command->name,
            label: $command->label,
            type: $command->type,
            required: $command->required,
            sortOrder: $command->sortOrder,
            options: $command->options,
            definitionId: $command->definitionId,
        );

        $this->definitionRepository->save($definition);
    }
}
