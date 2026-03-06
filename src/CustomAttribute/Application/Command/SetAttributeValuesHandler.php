<?php

declare(strict_types=1);

namespace GardenManager\CustomAttribute\Application\Command;

use GardenManager\CustomAttribute\Domain\Entity\CustomAttributeValue;
use GardenManager\CustomAttribute\Domain\Persistence\CustomAttributeDefinitionRepositoryInterface;
use GardenManager\CustomAttribute\Domain\Persistence\CustomAttributeValueRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Ulid;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class SetAttributeValuesHandler
{
    public function __construct(
        private CustomAttributeDefinitionRepositoryInterface $definitionRepository,
        private CustomAttributeValueRepositoryInterface $valueRepository,
    ) {
    }

    public function __invoke(SetAttributeValuesCommand $command): void
    {
        $definitions = $this->definitionRepository->findByEntityType($command->entityType);
        $existingValues = $this->valueRepository->findIndexedByDefinitionForEntity(
            $command->entityType,
            $command->entityId,
        );

        foreach ($definitions as $definition) {
            $definitionId = $definition->getId()->toString();
            $newValue = $command->values[$definitionId] ?? null;
            $existingValue = $existingValues[$definitionId] ?? null;

            $isEmpty = $newValue === null || $newValue === '' || $newValue === [];

            if ($isEmpty && $existingValue !== null) {
                $this->valueRepository->remove($existingValue);

                continue;
            }

            if ($isEmpty) {
                continue;
            }

            if ($existingValue !== null) {
                $existingValue->updateValue($newValue);
                $this->valueRepository->save($existingValue);
            } else {
                $value = CustomAttributeValue::create(
                    definition: $definition,
                    entityType: $command->entityType,
                    entityId: $command->entityId,
                    value: $newValue,
                );

                $this->valueRepository->save($value);
            }
        }
    }
}
