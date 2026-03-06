<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Application\Command;

use GardenManager\CustomAttribute\Domain\Persistence\CustomAttributeDefinitionRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class UpdateDefinitionHandler
{
    public function __construct(
        private CustomAttributeDefinitionRepositoryInterface $definitionRepository,
    ) {
    }

    public function __invoke(UpdateDefinitionCommand $command): void
    {
        $definition = $this->definitionRepository->getById($command->definitionId);

        $definition->update(
            label: $command->label,
            required: $command->required,
            sortOrder: $command->sortOrder,
            options: $command->options,
        );

        $this->definitionRepository->save($definition);
    }
}
